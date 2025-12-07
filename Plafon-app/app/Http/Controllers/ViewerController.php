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
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
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
            
            // Baca file CSV
            $csv = array_map('str_getcsv', file($path));
            
            if (empty($csv)) {
                return redirect()->back()->with('error', 'File CSV kosong');
            }

            // Ambil header (baris pertama)
            $header = array_map('trim', $csv[0]);
            
            // Validasi header yang diperlukan
            $requiredHeaders = ['kode_customer', 'nama', 'nama_kios', 'alamat', 'plafon_aktif', 'sales_id'];
            $missingHeaders = array_diff($requiredHeaders, $header);
            
            if (!empty($missingHeaders)) {
                return redirect()->back()->with('error', 
                    'Header CSV tidak lengkap. Header yang diperlukan: ' . implode(', ', $requiredHeaders));
            }

            // Hapus header dari array
            array_shift($csv);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $skippedCount = 0;

            DB::beginTransaction();

            foreach ($csv as $rowIndex => $row) {
                $lineNumber = $rowIndex + 2; // +2 karena index 0 adalah header dan row mulai dari 1
                
                // Skip baris kosong
                if (empty(array_filter($row))) {
                    $skippedCount++;
                    continue;
                }

                // Gabungkan header dengan data
                $data = array_combine($header, $row);

                // Bersihkan data dari whitespace
                $data = array_map('trim', $data);

                // Validasi data per baris
                $rowValidator = Validator::make($data, [
                    'kode_customer' => 'required|string|max:255',
                    'nama' => 'required|string|max:255',
                    'nama_kios' => 'required|string|max:255',
                    'alamat' => 'required|string',
                    'plafon_aktif' => 'required|numeric|min:0',
                    'sales_id' => 'required|integer|exists:users,id',
                ]);

                if ($rowValidator->fails()) {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: " . $rowValidator->errors()->first();
                    continue;
                }

                // Cek apakah sales_id valid (harus role sales)
                $sales = User::find($data['sales_id']);
                if (!$sales || $sales->role !== 'sales') {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: Sales ID {$data['sales_id']} tidak valid atau bukan role sales";
                    continue;
                }

                try {
                    // Update atau Insert customer
                    Customer::updateOrCreate(
                        ['kode_customer' => $data['kode_customer']], // Kondisi pencarian
                        [
                            'sales_id' => $data['sales_id'],
                            'nama' => $data['nama'],
                            'nama_kios' => $data['nama_kios'],
                            'alamat' => $data['alamat'],
                            'plafon_aktif' => $data['plafon_aktif'],
                            'status' => $data['status'] ?? 'active', // Default active jika tidak ada
                        ]
                    );
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Buat pesan response
            $message = "Import selesai: {$successCount} data berhasil";
            
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} baris kosong dilewati";
            }
            
            if ($errorCount > 0) {
                $message .= ", {$errorCount} data gagal";
            }

            // Jika ada error, tampilkan detail error (maksimal 10 error pertama)
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
