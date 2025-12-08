<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

    /**
     * Import customers from CSV file
     */
    public function import(Request $request)
    {
        // Validasi file upload
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'File CSV wajib diunggah',
            'csv_file.mimes' => 'File harus berformat CSV',
            'csv_file.max' => 'Ukuran file maksimal 5MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', $validator->errors()->first());
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            
            // Baca CSV
            $csv = array_map('str_getcsv', file($path));
            
            if (empty($csv)) {
                return redirect()->back()->with('error', 'File CSV kosong');
            }

            // Mapping header CSV ke kolom database
            $headerMapping = [
                'kode' => 'kode_customer',
                'kontak' => 'nama',
                'perusahaan' => 'nama_kios',
                'alamat1' => 'alamat',
                'bataskredit' => 'plafon_aktif',
                'kategori' => 'nama_sales',
            ];

            // Header CSV (trim whitespace)
            $csvHeaders = array_map('trim', $csv[0]);
            
            // Buat mapping index: dari index CSV ke nama kolom database
            $columnIndexMap = [];
            foreach ($csvHeaders as $index => $csvHeader) {
                $csvHeaderLower = strtolower($csvHeader);
                
                // Cek apakah header CSV ada di mapping
                if (isset($headerMapping[$csvHeaderLower])) {
                    $columnIndexMap[$index] = $headerMapping[$csvHeaderLower];
                }
                // Kolom yang tidak ada di mapping akan diabaikan
            }

            // Cek apakah ada minimal satu kolom yang ter-mapping
            if (empty($columnIndexMap)) {
                return redirect()->back()->with('error', 
                    'Tidak ada kolom yang cocok dengan mapping. Pastikan CSV memiliki kolom: ' . 
                    implode(', ', array_keys($headerMapping))
                );
            }

            // Hapus header dari array
            array_shift($csv);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $skippedCount = 0;

            DB::beginTransaction();

            foreach ($csv as $rowIndex => $row) {
                $lineNumber = $rowIndex + 2;

                // Skip baris kosong
                if (empty(array_filter($row))) {
                    $skippedCount++;
                    continue;
                }

                // Map data dari CSV ke struktur database
                $mappedData = [];
                foreach ($columnIndexMap as $csvIndex => $dbColumn) {
                    if (isset($row[$csvIndex])) {
                        $mappedData[$dbColumn] = trim($row[$csvIndex]);
                    }
                }

                // Set status default ke 'active'
                $mappedData['status'] = 'active';

                // Validasi kolom wajib
                $requiredFields = ['kode_customer', 'nama', 'nama_kios', 'alamat'];
                $missingFields = array_diff($requiredFields, array_keys($mappedData));

                if (!empty($missingFields)) {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: Kolom wajib tidak ada - " . implode(', ', $missingFields);
                    continue;
                }

                // Validasi data
                $rowValidator = Validator::make($mappedData, [
                    'kode_customer' => 'required|string|max:255',
                    'nama' => 'required|string|max:255',
                    'nama_kios' => 'required|string|max:255',
                    'alamat' => 'required|string',
                    'plafon_aktif' => 'nullable|numeric|min:0',
                    'nama_sales' => 'nullable|string|max:255',
                ]);

                if ($rowValidator->fails()) {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: " . $rowValidator->errors()->first();
                    continue;
                }

                try {
                    // Cari sales_id berdasarkan nama_sales jika ada
                    $salesId = null;
                    if (!empty($mappedData['nama_sales'])) {
                        $sales = User::where('role', 'sales')
                            ->where('name', 'LIKE', '%' . $mappedData['nama_sales'] . '%')
                            ->first();
                        
                        if ($sales) {
                            $salesId = $sales->id;
                        } else {
                            $errorCount++;
                            $errors[] = "Baris {$lineNumber}: Sales '{$mappedData['nama_sales']}' tidak ditemukan";
                            continue;
                        }
                    } else {
                        // Jika nama_sales tidak ada, cari sales pertama atau gunakan default
                        $sales = User::where('role', 'sales')->first();
                        if ($sales) {
                            $salesId = $sales->id;
                        } else {
                            $errorCount++;
                            $errors[] = "Baris {$lineNumber}: Tidak ada sales yang tersedia di sistem";
                            continue;
                        }
                    }

                    // Siapkan data untuk insert/update
                    $customerData = [
                        'sales_id' => $salesId,
                        'nama' => $mappedData['nama'],
                        'nama_kios' => $mappedData['nama_kios'],
                        'alamat' => $mappedData['alamat'],
                        'plafon_aktif' => $mappedData['plafon_aktif'] ?? 0,
                        'nama_sales' => $mappedData['nama_sales'] ?? null,
                        'status' => 'active',
                    ];

                    Customer::updateOrCreate(
                        ['kode_customer' => $mappedData['kode_customer']],
                        $customerData
                    );

                    $successCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Import selesai: {$successCount} data berhasil";

            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} baris kosong dilewati";
            }

            if ($errorCount > 0) {
                $message .= ", {$errorCount} data gagal";
            }

            if (!empty($errors)) {
                $errorDetails = implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $errorDetails .= "\n... dan " . (count($errors) - 10) . " error lainnya";
                }

                return redirect()->back()
                    ->with('warning', $message)
                    ->with('error_details', $errorDetails);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
