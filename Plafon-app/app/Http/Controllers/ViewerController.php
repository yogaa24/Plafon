<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ViewerController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::whereIn('status', ['approved_3', 'done'])
            ->with(['sales', 'approvals.approver', 'previousSubmission']); // ← Tambah previousSubmission

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%")
                  ->orWhereHas('sales', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $submissions = $query->paginate(15)->appends($request->query());

        // Hitung pengajuan yang belum Done (status = approved_3)
        $pendingCount = Submission::where('status', 'approved_3')->count();

        // Get all sales for filter dropdown
        $salesList = \App\Models\User::where('role', 'sales')->get();

        return view('viewer.index', compact('submissions', 'salesList', 'pendingCount'));
    }

    public function show(Submission $submission)
    {
        // Only show if approved or done
        if (!in_array($submission->status, ['approved_3', 'done'])) {
            abort(403, 'Anda hanya dapat melihat pengajuan yang sudah disetujui.');
        }

        $submission->load(['sales', 'approvals.approver', 'previousSubmission']);

        return view('viewer.show', compact('submission'));
    }

    public function markDone(Submission $submission)
    {
        // Hanya ubah dari approved_3 → done
        if ($submission->status !== 'approved_3') {
            return back()->with('error', 'Status tidak dapat diubah.');
        }

        $submission->update([
            'status' => 'done'
        ]);

        return back()->with('success', 'Status berhasil diubah menjadi Done.');
    }

    public function import(Request $request)
    {
        // Validasi file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
        ], [
            'csv_file.required' => 'File CSV harus diupload',
            'csv_file.mimes' => 'File harus berformat CSV',
            'csv_file.max' => 'Ukuran file maksimal 5MB'
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        try {
            $file = $request->file('csv_file');
            $skipDuplicates = $request->has('skip_duplicates');
            
            // Baca file CSV dengan encoding UTF-8
            $handle = fopen($file->getRealPath(), 'r');
            
            // Baca header (baris pertama)
            $header = fgetcsv($handle);
            
            if (!$header) {
                fclose($handle);
                return back()->with('error', 'File CSV kosong atau tidak valid');
            }
            
            // Bersihkan header dari spasi dan karakter tersembunyi
            $header = array_map(function($col) {
                return trim(strtolower(str_replace(["\r", "\n", "\t"], '', $col)));
            }, $header);
            
            $headerCount = count($header);
            
            // Validasi kolom yang diperlukan
            $requiredColumns = ['nama', 'nama_kios', 'alamat', 'plafon', 'jumlah_buka_faktur', 'komitmen_pembayaran'];
            $missingColumns = array_diff($requiredColumns, $header);
            
            if (!empty($missingColumns)) {
                fclose($handle);
                return back()->with('error', 'Kolom yang diperlukan tidak ditemukan: ' . implode(', ', $missingColumns));
            }
            
            $imported = 0;
            $skipped = 0;
            $errors = [];
            $lineNumber = 1; // Mulai dari 1 karena header
            
            // Baca data baris per baris
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                // Skip baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Cek jumlah kolom
                $rowCount = count($row);
                
                // Jika jumlah kolom tidak sama, skip atau tambahkan kolom kosong
                if ($rowCount !== $headerCount) {
                    if ($rowCount < $headerCount) {
                        // Tambahkan kolom kosong jika kurang
                        $row = array_pad($row, $headerCount, '');
                    } else {
                        // Potong jika lebih
                        $row = array_slice($row, 0, $headerCount);
                    }
                }
                
                try {
                    // Kombinasikan header dengan data
                    $data = array_map(function($v) {
                        return trim($v, "\" \t\n\r");
                    }, array_combine($header, $row));
                    
                    // Validasi data wajib
                    if (empty(trim($data['nama'])) || empty(trim($data['nama_kios']))) {
                        $errors[] = "Baris {$lineNumber}: Nama atau Nama Kios kosong";
                        continue;
                    }
                    
                    // Cek duplikat berdasarkan nama_kios
                    if ($skipDuplicates) {
                        $exists = Submission::where('nama_kios', trim($data['nama_kios']))->exists();
                        if ($exists) {
                            $skipped++;
                            continue;
                        }
                    }
                    
                    // Generate kode unik
                    $kode = 'SUB-' . strtoupper(substr(md5(uniqid() . time() . $lineNumber), 0, 8));
                    
                    // Pastikan kode unik
                    while (Submission::where('kode', $kode)->exists()) {
                        $kode = 'SUB-' . strtoupper(substr(md5(uniqid() . time() . rand()), 0, 8));
                    }
                    
                    // Bersihkan dan konversi data plafon
                    $plafonValue = preg_replace('/[^0-9.]/', '', $data['plafon']);
                    $plafonValue = floatval($plafonValue);
                    
                    if ($plafonValue <= 0) {
                        $errors[] = "Baris {$lineNumber}: Plafon tidak valid";
                        continue;
                    }
                    
                    // Buat submission baru
                    Submission::create([
                        'kode' => $kode,
                        'nama' => trim($data['nama']),
                        'nama_kios' => trim($data['nama_kios']),
                        'alamat' => trim($data['alamat'] ?? ''),
                        'plafon' => $plafonValue,
                        'plafon_type' => 'open',
                        'jumlah_buka_faktur' => intval($data['jumlah_buka_faktur'] ?? 0),
                        'komitmen_pembayaran' => trim($data['komitmen_pembayaran'] ?? ''),
                        'sales_id' => Auth::id(),
                        'status' => 'done',
                        'current_level' => 3,
                    ]);
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
                }
            }
            
            fclose($handle);
            
            // Buat pesan hasil
            $message = "Berhasil import {$imported} data";
            if ($skipped > 0) {
                $message .= ", {$skipped} data dilewati (duplikat)";
            }
            if (!empty($errors)) {
                $message .= ". Gagal: " . count($errors) . " baris";
                // Log error untuk debugging
                \Log::warning('CSV Import Errors:', $errors);
            }
            
            if ($imported > 0) {
                return back()->with('success', $message);
            } else {
                return back()->with('error', 'Tidak ada data yang berhasil diimport. ' . (count($errors) > 0 ? 'Error: ' . implode('; ', array_slice($errors, 0, 3)) : ''));
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }
}