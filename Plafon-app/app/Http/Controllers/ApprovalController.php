<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\Level3DoneExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        // Redirect berdasarkan level
        if ($level == 3) {
            return redirect()->route('approvals.level3');
        } elseif ($level == 4) {
            return redirect()->route('approvals.level4');
        } elseif ($level == 5) {
            return redirect()->route('approvals.level5');
        } elseif ($level == 6) {
            return redirect()->route('approvals.level6');
        }

        $query = Submission::where('current_level', $level)
            ->whereIn('status', ['pending', 'approved_1', 'approved_2'])
            ->with(['sales', 'approvals.approver', 'previousSubmission', 'customer']);

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

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $submissions = $query->paginate(15)->appends($request->query());

        $submissionsArray = $submissions->map(function($submission) {
            // Decode payment_data jika masih string
            $paymentData = $submission->payment_data;
            if (is_string($paymentData)) {
                $paymentData = json_decode($paymentData, true);
            }
            
            return [
                'id' => $submission->id,
                'kode' => $submission->kode,
                'nama' => $submission->nama,
                'nama_kios' => $submission->nama_kios,
                'plafon_type' => $submission->plafon_type,
                
                // PENTING: Ini field yang dibutuhkan untuk calculation
                'plafon' => $submission->plafon, // Plafon aktif customer
                'jumlah_buka_faktur' => $submission->jumlah_buka_faktur, // Value faktur
                
                'payment_type' => $submission->payment_type,
                'payment_data' => $paymentData,
                
                // Data customer jika ada
                'customer' => $submission->customer ? [
                    'id' => $submission->customer->id,
                    'plafon_aktif' => $submission->customer->plafon_aktif,
                ] : null,
            ];
        })->toArray();

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();
        
        return view('approvals.index', compact('level', 'submissions', 'submissionsArray', 'salesList'));
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        // Semua level approver bisa akses history
        if (!in_array($level, [1, 2, 3, 4, 5, 6])) {
            abort(403, 'Unauthorized access');
        }

        // Query pengajuan yang sudah diproses oleh approver ini
        $query = Approval::where('approver_id', $user->id)
            ->where('level', $level)
            ->with(['submission.sales', 'submission.customer', 'submission.approvals.approver'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('submission', function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('nama_kios', 'like', "%{$search}%")
                ->orWhereHas('sales', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status (approved/rejected)
        if ($request->filled('status_filter')) {
            $query->where('status', $request->status_filter);
        }

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->whereHas('submission', function($q) use ($request) {
                $q->where('sales_id', $request->sales_id);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $approvals = $query->paginate(15)->appends($request->query());

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('approvals.history', [
            'approvals' => $approvals,
            'level' => $level,
            'salesList' => $salesList,
        ]);
    }

    // Dashboard khusus Level 3
    public function level3(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver3') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 3)
            ->whereIn('status', ['approved_2', 'approved_3'])
            ->with(['sales', 'approvals.approver', 'customer', 'previousSubmission']);

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

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');
        $submissions = $query->paginate(15)->appends($request->query());

        $submissionsArray = $submissions->map(function($s) {
            return [
                'id' => $s->id,
                'plafon_type' => $s->plafon_type,
                'payment_type' => $s->payment_type ?? 'od',
                'payment_data' => $s->payment_data ?? []
            ];
        })->toArray();

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('approvals.level3', [
            'submissions' => $submissions,
            'submissionsArray' => $submissionsArray,
            'level' => 3,
            'salesList' => $salesList,
        ]);
    }

    public function level4(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver4') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 4)
            ->whereIn('status', ['approved_3'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter
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

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 4,
            'salesList' => $salesList,
        ]);
    }

    public function level5(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver5') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 5)
            ->whereIn('status', ['approved_4'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter
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

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 5,
            'salesList' => $salesList,
        ]);
    }

    public function level6(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver6') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 6)
            ->whereIn('status', ['approved_5'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter
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

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        // Get all sales for filter dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 6,
            'salesList' => $salesList,
        ]);
    }

    // Di ApprovalController.php
    public function exportLevel3(Request $request)
    {
        $user = Auth::user();
        
        // HANYA Approver Level 3 yang bisa export
        if ($user->role !== 'approver3') {
            abort(403, 'Unauthorized: Hanya Approver Level 3 yang dapat mengekspor data.');
        }
    
        // Query pengajuan yang SUDAH MELEWATI Level 3 ke atas
        $query = Submission::whereIn('status', [
                'approved_3',           // Di Level 4
                'approved_4',    // Di Level 4
                'approved_5',    // Di Level 5
                'approved_6',    // Di Level 6
                'pending_viewer',       // Di Viewer (Proses Input)
                'done'                  // Selesai
            ])
            ->with(['sales', 'customer', 'previousSubmission'])
            ->with(['approvals' => function($q) {
                // Load approvals dari Level 3 sampai 6
                $q->whereIn('level', [3, 4, 5, 6])->with('approver');
            }]);
    
        // Apply filter search, date, sales jika ada
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        // Filter by sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }
    
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
    
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    
        $submissions = $query->orderBy('created_at', 'desc')->get();
    
        // Tidak perlu kirim daftar approver, karena akan diambil dari approval records
        $filename = 'Pengajuan_Level3_Keatas_' . date('Y-m-d_His') . '.xlsx';
    
        return Excel::download(
            new Level3DoneExport($submissions), 
            $filename
        );
    }

     /**
     * Compress image to max 500KB
     */
    private function compressImage($file)
    {
        // Load image based on type
        $image = imagecreatefromstring(file_get_contents($file->path()));
        
        if (!$image) {
            throw new \Exception('Failed to load image');
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Calculate new dimensions (max 1920px width)
        $maxWidth = 1920;
        $maxHeight = 1080;
        
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            
            // Create new image with calculated dimensions
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }
        
        // Compress until size <= 500KB
        $quality = 85;
        $targetSize = 500 * 1024; // 500KB in bytes
        
        do {
            ob_start();
            imagejpeg($image, null, $quality);
            $imageData = ob_get_clean();
            $fileSize = strlen($imageData);
            
            if ($fileSize <= $targetSize) {
                break;
            }
            
            $quality -= 5;
        } while ($quality > 10);
        
        imagedestroy($image);
        
        return $imageData;
    }

    public function process(Request $request, Submission $submission)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);
        $action = $request->input('action');

        // Validasi level approver
        if ($submission->current_level != $level) {
            return redirect()->back()->with('error', 'Anda tidak berhak melakukan approval pada level ini');
        }

        // Validasi khusus untuk Level 2 saat approve
        if ($level == 2 && $action === 'approved') {
    
            $request->validate([
                'lampiran' => 'nullable|array|max:3',
                'lampiran.*' => 'image|mimes:jpeg,jpg,png|max:10240', // Max 10MB sebelum compress
            ], [
                'lampiran.array' => 'Lampiran harus berupa array',
                'lampiran.max' => 'Maksimal 3 gambar',
                'lampiran.*.image' => 'File harus berupa gambar',
                'lampiran.*.mimes' => 'Format gambar harus jpeg, jpg, atau png',
                'lampiran.*.max' => 'Ukuran gambar maksimal 10MB',
            ]);
        
            // Handle upload multiple gambar dengan compress
            if ($request->hasFile('lampiran')) {
                // Hapus lampiran lama jika ada
                if ($submission->lampiran_path) {
                    $oldPaths = json_decode($submission->lampiran_path, true);
                    if (is_array($oldPaths)) {
                        foreach ($oldPaths as $oldPath) {
                            \Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
        
                $uploadedPaths = [];
                foreach ($request->file('lampiran') as $file) {
                    // Compress gambar
                    $compressedImage = $this->compressImage($file);
                    
                    // Generate unique filename
                    $filename = 'lampiran-' . time() . uniqid() . '.jpg';
                    $path = 'lampiran-submissions/' . $filename;
                    
                    // Save compressed image
                    \Storage::disk('public')->put($path, $compressedImage);
                    $uploadedPaths[] = $path;
                }
                
                $submission->lampiran_path = json_encode($uploadedPaths);
            }
        
            // Validasi payment data HANYA untuk Open Plafon
            if ($submission->plafon_type === 'open') {
                $request->validate([
                    'piutang' => 'required|numeric|min:0',
                    'jml_over' => 'required|numeric',
                    'jml_od_30' => 'required|numeric|min:0',
                    'jml_od_60' => 'required|numeric|min:0',
                    'jml_od_90' => 'required|numeric|min:0',
                ], [
                    'piutang.required' => 'Piutang wajib diisi',
                    'jml_over.required' => 'Jumlah Over wajib diisi',
                    'jml_od_30.required' => 'Jumlah OD 30 wajib diisi',
                    'jml_od_60.required' => 'Jumlah OD 60 wajib diisi',
                    'jml_od_90.required' => 'Jumlah OD 90 wajib diisi',
                ]);
        
                $paymentData = [
                    'piutang' => $request->piutang,
                    'jml_over' => $request->jml_over,
                    'od_30' => $request->jml_od_30,
                    'od_60' => $request->jml_od_60,
                    'od_90' => $request->jml_od_90,
                ];
        
                $submission->payment_data = json_encode($paymentData);
            }
        }

        // Khusus level 3: cek apakah user sudah pernah memberikan approval
        if ($level == 3) {
            $existingApproval = Approval::where('submission_id', $submission->id)
                ->where('approver_id', $user->id)
                ->where('level', 3)
                ->first();

            if ($existingApproval) {
                return redirect()->back()->with('error', 'Anda sudah memberikan keputusan pada pengajuan ini');
            }
        }

        DB::beginTransaction();
        try {
            // Validasi NOTES WAJIB untuk SEMUA level dan SEMUA action
            $request->validate([
                'note' => 'required|string|min:1'
            ], [
                'note.required' => 'Catatan wajib diisi',
                'note.min' => 'Catatan minimal 3 karakter'
            ]);

            // Validasi khusus untuk Level 2 saat approve (kode lama tetap)
            if ($level == 2 && $action === 'approved' && $submission->plafon_type === 'open') {
                $request->validate([
                    'piutang' => 'required|numeric|min:0',
                    'jml_over' => 'required|numeric',
                    'jml_od_30' => 'required|numeric|min:0',
                    'jml_od_60' => 'required|numeric|min:0',
                    'jml_od_90' => 'required|numeric|min:0',
                ]);

                $paymentData = [
                    'piutang' => $request->piutang,
                    'jml_over' => $request->jml_over,
                    'od_30' => $request->jml_od_30,
                    'od_60' => $request->jml_od_60,
                    'od_90' => $request->jml_od_90,
                ];

                $submission->payment_data = json_encode($paymentData);
            }

            // Buat record approval
            $approval = new Approval();
            $approval->submission_id = $submission->id;
            $approval->approver_id = $user->id;
            $approval->level = $level;
            $approval->status = $action;
            $approval->note = $request->input('note'); // WAJIB ADA

            if ($level == 2 && $action === 'approved' && $submission->plafon_type === 'open') {
                $approval->piutang = $request->piutang;
                $approval->jml_over = $request->jml_over;
                $approval->jml_od_30 = $request->jml_od_30;
                $approval->jml_od_60 = $request->jml_od_60;
                $approval->jml_od_90 = $request->jml_od_90;
            }

            $approval->save();

            // ===== LOGIKA BARU SESUAI REQUIREMENT =====
            if ($level == 1) {
                if ($action === 'approved') {
                    $submission->status = 'approved_1';
                    $submission->current_level = 2;
                } elseif ($action === 'rejected') {
                    $submission->status = 'rejected';
                    $submission->rejection_note = $request->input('note');
                }
            } 
            elseif ($level == 2) {
                if ($action === 'approved') {
                    $submission->status = 'approved_2';
                    $submission->current_level = 3;
                } elseif ($action === 'rejected') {
                    $submission->status = 'rejected';
                    $submission->rejection_note = $request->input('note');
                }
            }
            elseif ($level == 3) {
                // Level 3: APPROVE/REJECT tetap lanjut ke Level 4 
                $submission->status = 'approved_3';
                $submission->current_level = 4;
            }
            elseif ($level == 4) {
                // Cek apakah Level 3 ada yang reject
                $level3Rejected = $submission->approvals
                    ->where('level', 3)
                    ->where('status', 'rejected')
                    ->count() > 0;
                
                // Cek apakah Level 4 reject
                $level4Rejected = ($action === 'rejected');
                
                if ($action === 'approved') {
                    // Level 4 APPROVE
                    if ($level3Rejected) {
                        // Jika Level 3 ada yang reject → Lanjut ke Level 5
                        $submission->status = 'approved_4';
                        $submission->current_level = 5;
                    } else {
                        // Jika Level 3 SEMUA approve DAN Level 4 approve → Langsung ke Viewer
                        $submission->status = 'pending_viewer';
                        $submission->current_level = null;
                    }
                } else {
                    // Level 4 REJECT → Lanjut ke Level 5
                    $submission->status = 'approved_4';
                    $submission->current_level = 5;
                }
            }
            elseif ($level == 5) {
                if ($action === 'approved') {
                    // Approve → langsung ke Viewer
                    $submission->status = 'pending_viewer';
                    $submission->current_level = null; // Set NULL
                } else {
                    // Reject → lanjut ke Level 6
                    $submission->status = 'approved_5';
                    $submission->current_level = 6;
                }
            }
            elseif ($level == 6) {
                if ($action === 'approved') {
                    // Approve → Selesai
                    $submission->status = 'pending_viewer';
                    $submission->current_level = null; // Set NULL
                } else {
                    // Reject → Kembali ke Sales
                    $submission->status = 'rejected';
                    $submission->current_level = 1;
                    $submission->rejection_note = $request->input('note');
                }
            }

            $submission->save();

            DB::commit();

            $message = match($action) {
                'approved' => 'Pengajuan berhasil disetujui',
                'rejected' => 'Pengajuan berhasil ditolak',
                default => 'Proses approval berhasil'
            };

            // Redirect sesuai level
            if ($level == 3) {
                return redirect()->route('approvals.level3')->with('success', $message);
            } elseif ($level == 4) {
                return redirect()->route('approvals.level4')->with('success', $message);
            } elseif ($level == 5) {
                return redirect()->route('approvals.level5')->with('success', $message);
            } elseif ($level == 6) {
                return redirect()->route('approvals.level6')->with('success', $message);
            }

            return redirect()->route('approvals.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    private function getApproverLevel($role)
    {
         return match($role) {
            'approver1' => 1,
            'approver2' => 2,
            'approver3' => 3,
            'approver4' => 4,
            'approver5' => 5,
            'approver6' => 6,
            default => 0
        };
    }
}