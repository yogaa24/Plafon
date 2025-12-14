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

        $submissions = $query->paginate(15);

        $submissionsArray = $submissions->map(function($s) {
            return [
                'id' => $s->id,
                'plafon_type' => $s->plafon_type,
                'payment_type' => $s->payment_type ?? 'od',
                'payment_data' => $s->payment_data ?? []
            ];
        })->toArray();

        return view('approvals.index', [
            'submissions' => $submissions,
            'submissionsArray' => $submissionsArray,
            'level' => $level,
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
            ->whereIn('status', ['approved_2', 'approved_3', 'pending_approver3'])
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');
        $submissions = $query->paginate(15);

        $submissionsArray = $submissions->map(function($s) {
            return [
                'id' => $s->id,
                'plafon_type' => $s->plafon_type,
                'payment_type' => $s->payment_type ?? 'od',
                'payment_data' => $s->payment_data ?? []
            ];
        })->toArray();

        return view('approvals.level3', [
            'submissions' => $submissions,
            'submissionsArray' => $submissionsArray,
            'level' => 3,
        ]);
    }

    public function level4(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver4') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 4)
            ->whereIn('status', ['approved_3', 'approver_4'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter (sama seperti index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 4,
        ]);
    }

    public function level5(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver5') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 5)
            ->whereIn('status', ['approver_5'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 5,
        ]);
    }

    public function level6(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'approver6') {
            abort(403, 'Unauthorized access');
        }

        $query = Submission::where('current_level', 6)
            ->whereIn('status', ['approver_6'])
            ->with(['sales', 'approvals.approver', 'customer']);

        // Search & Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('approvals.level456', [
            'submissions' => $submissions,
            'level' => 6,
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
                'pending_approver4',    // Di Level 4
                'pending_approver5',    // Di Level 5
                'pending_approver6',    // Di Level 6
                'pending_viewer',       // Di Viewer (Proses Input)
                'done'                  // Selesai
            ])
            ->with(['sales', 'customer', 'previousSubmission'])
            ->with(['approvals' => function($q) {
                // Load approvals dari Level 3 sampai 6
                $q->whereIn('level', [3, 4, 5, 6])->with('approver');
            }]);
    
        // Apply filter search, date jika ada
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%");
            });
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
        if ($level == 2 && $action === 'approved' && $submission->plafon_type === 'open') {
            $request->validate([
                'piutang' => 'required|numeric|min:0',
                'jml_over' => 'required|numeric|min:0',
                'jml_od_30' => 'required|numeric|min:0',
                'jml_od_60' => 'required|numeric|min:0',
                'jml_od_90' => 'required|numeric|min:0',
                'lampiran' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ], [
                'piutang.required' => 'Piutang wajib diisi',
                'jml_over.required' => 'Jumlah Over wajib diisi',
                'jml_od_30.required' => 'Jumlah OD 30 wajib diisi',
                'jml_od_60.required' => 'Jumlah OD 60 wajib diisi',
                'jml_od_90.required' => 'Jumlah OD 90 wajib diisi',
                'lampiran.image' => 'File harus berupa gambar',
                'lampiran.mimes' => 'Format gambar harus jpeg, jpg, atau png',
                'lampiran.max' => 'Ukuran gambar maksimal 2MB',
            ]);

            $paymentData = [
                'piutang' => $request->piutang,
                'jml_over' => $request->jml_over,
                'od_30' => $request->jml_od_30,
                'od_60' => $request->jml_od_60,
                'od_90' => $request->jml_od_90,
            ];

            $submission->payment_data = json_encode($paymentData);
            
            // Handle upload gambar
            if ($request->hasFile('lampiran')) {
                // Hapus gambar lama jika ada
                if ($submission->lampiran_path) {
                    \Storage::disk('public')->delete($submission->lampiran_path);
                }
                $submission->lampiran_path = $request->file('lampiran')->store('lampiran-submissions', 'public');
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
                    'jml_over' => 'required|numeric|min:0',
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
                // Level 3: APPROVE/REJECT tetap lanjut ke Level 4 // PENTING: Status berubah ke approver4,
                $this->handleLevel3Approval($submission, $action);
                $submission->status = 'approver_4';
                $submission->current_level = 4;
            }
            elseif ($level == 4) {
                if ($action === 'approved') {
                    // Approve → langsung ke Viewer
                    $submission->status = 'pending_viewer';
                    $submission->current_level = null; // Set NULL karena sudah selesai approval
                } else {
                    // Reject → lanjut ke Level 5
                    $submission->status = 'approver_5';
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
                    $submission->status = 'approver_6';
                    $submission->current_level = 6;
                }
            }
            elseif ($level == 6) {
                if ($action === 'approved') {
                    // Approve → Selesai
                    $submission->status = 'done';
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

    private function handleLevel3Approval(Submission $submission, string $action)
    {
        // Level 3 logic tetap sama untuk tracking votes
        $level3Approvals = Approval::where('submission_id', $submission->id)
            ->where('level', 3)
            ->get();

        $approvedCount = $level3Approvals->where('status', 'approved')->count();
        $rejectedCount = $level3Approvals->where('status', 'rejected')->count();

        // Hanya untuk tracking, status akan di-set di method process()
        if ($approvedCount >= 1) {
            $submission->status = 'approved_3';
        } elseif ($rejectedCount >= 4) {
            $submission->status = 'rejected_all_level3';
        }
    }

    public function updateCommitment(Request $request, Submission $submission)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        // Hanya Level 1, 2, 3 yang bisa edit
        if (!in_array($level, [1, 2, 3])) {
            return redirect()->back()->with('error', 'Anda tidak berhak mengedit komitmen pembayaran');
        }

        // Validasi hanya bisa edit jika sedang di level tersebut
        if ($submission->current_level != $level) {
            return redirect()->back()->with('error', 'Komitmen hanya bisa diedit saat pengajuan di level Anda');
        }

        $request->validate([
            'komitmen_pembayaran' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $oldCommitment = $submission->komitmen_pembayaran;
            $newCommitment = $request->komitmen_pembayaran;

            // Update komitmen
            $submission->komitmen_pembayaran = $newCommitment;
            
            // Simpan log edit
            $commitmentLog = json_decode($submission->commitment_edit_log, true) ?? [];
            $commitmentLog[] = [
                'editor_id' => $user->id,
                'editor_name' => $user->name,
                'editor_level' => $level,
                'old_value' => $oldCommitment,
                'new_value' => $newCommitment,
                'edited_at' => now()->toDateTimeString(),
            ];
            
            $submission->commitment_edit_log = json_encode($commitmentLog);
            $submission->save();

            DB::commit();

            return redirect()->back()->with('success', 'Komitmen pembayaran berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupdate komitmen: ' . $e->getMessage());
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