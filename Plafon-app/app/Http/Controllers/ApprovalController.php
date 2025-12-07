<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        // Redirect ke dashboard level 3 jika user adalah approver level 3
        if ($level == 3) {
            return redirect()->route('approvals.level3');
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
            'submissions' => $submissions,   // JANGAN DIHAPUS
            'submissionsArray' => $submissionsArray,
            'level' => $level,
        ]);
    }

    // Dashboard khusus Level 3
    public function level3(Request $request)
    {
        $user = Auth::user();
        
        // Pastikan user adalah approver level 3
        if (!$user->is_level3_approver || $user->role !== 'approver3') {
            abort(403, 'Unauthorized access');
        }

        // Get all level 3 approvers
        $level3Approvers = User::where('is_level3_approver', true)
            ->where('role', 'approver3')
            ->orderBy('approver_name')
            ->get();

        // Filter status (default: on_progress)
        $statusFilter = $request->get('status', 'on_progress');

        $query = Submission::where('current_level', 3)
        ->with(['sales', 'customer', 'previousSubmission'])
        ->with(['approvals' => function($q) {
            $q->where('level', 3)->with('approver');
        }]);

        // Apply status filter
        if ($statusFilter === 'on_progress') {
            // On progress: pengajuan yang masih menunggu (approved_2) atau sedang diproses level 3 (approved_3) 
            // tapi belum ada yang approve
            $query->where(function($q) {
                $q->where('status', 'approved_2')
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'approved_3')
                           ->whereDoesntHave('approvals', function($approvalQ) {
                               $approvalQ->where('level', 3)
                                        ->where('status', 'approved');
                           });
                  });
            });
        } elseif ($statusFilter === 'done') {
            // Done: pengajuan dengan status approved_3 yang sudah ada minimal 1 approval, 
            // atau status done (untuk data lama)
            $query->where(function($q) {
                $q->where('status', 'done')
                  ->orWhere(function($subQ) {
                      $subQ->where('status', 'approved_3')
                           ->whereHas('approvals', function($approvalQ) {
                               $approvalQ->where('level', 3)
                                        ->where('status', 'approved');
                           });
                  });
            });
        }

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

        $query->orderBy('created_at', 'desc');
        $submissions = $query->paginate(15);

        return view('approvals.level3', compact('submissions', 'level3Approvers', 'statusFilter'));
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
            // Validasi khusus untuk Level 2 saat approve
            if ($level == 2 && $action === 'approved' && $submission->plafon_type === 'open') {
                $request->validate([
                    'piutang' => 'required|numeric|min:0',
                    'jml_over' => 'required|numeric|min:0',
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

            // Validasi catatan untuk reject dan revision
            if (in_array($action, ['rejected', 'revision'])) {
                $request->validate([
                    'note' => 'required|string|min:3'
                ], [
                    'note.required' => 'Catatan wajib diisi',
                    'note.min' => 'Catatan minimal 3 karakter'
                ]);
            }

            // Buat record approval
            $approval = new Approval();
            $approval->submission_id = $submission->id;
            $approval->approver_id = $user->id;
            $approval->level = $level;
            $approval->status = $action;
            $approval->note = $request->input('note');

            if ($level == 2 && $action === 'approved' && $submission->plafon_type === 'open') {
                $approval->piutang = $request->piutang;
                $approval->jml_over = $request->jml_over;
                $approval->jml_od_30 = $request->jml_od_30;
                $approval->jml_od_60 = $request->jml_od_60;
                $approval->jml_od_90 = $request->jml_od_90;
            }

            $approval->save();

            // Update status submission berdasarkan level
            if ($level == 3) {
                // LOGIKA KHUSUS LEVEL 3
                $this->handleLevel3Approval($submission, $action);
            } else {
                // Level 1 & 2 (logika lama)
                if ($action === 'approved') {
                    $submission->status = 'approved_' . $level;
                    $submission->current_level = $level + 1;
                } elseif ($action === 'rejected') {
                    $submission->status = 'rejected';
                    $submission->rejection_note = $request->input('note');
                } elseif ($action === 'revision') {
                    $submission->status = 'revision';
                    $submission->revision_note = $request->input('note');
                    $submission->current_level = 1;
                }
            }

            $submission->save();

            DB::commit();

            $message = match($action) {
                'approved' => 'Pengajuan berhasil disetujui',
                'rejected' => 'Pengajuan berhasil ditolak',
                'revision' => 'Pengajuan dikembalikan untuk revisi',
                default => 'Proses approval berhasil'
            };

            if ($level == 3) {
                return redirect()->route('approvals.level3')->with('success', $message);
            }

            return redirect()->route('approvals.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function handleLevel3Approval(Submission $submission, string $action)
    {
        // Hitung jumlah approved dan rejected di level 3
        $level3Approvals = Approval::where('submission_id', $submission->id)
            ->where('level', 3)
            ->get();

        $approvedCount = $level3Approvals->where('status', 'approved')->count();
        $rejectedCount = $level3Approvals->where('status', 'rejected')->count();

        // RULE 1: Jika ada 1 approved, status menjadi approved_3 (bukan done)
        if ($approvedCount >= 1) {
            $submission->status = 'approved_3';
            return;
        }

        // RULE 2: Jika semua 4 rejected, kembali ke awal
        if ($rejectedCount >= 4) {
            $submission->status = 'rejected';
            $submission->current_level = 1;
            return;
        }

        // RULE 3: Jika masih ada yang belum vote atau belum ada yang approve dan belum 4 reject
        // Tetap di status approved_2 (menunggu di level 3)
        if ($submission->status === 'approved_2') {
            $submission->status = 'approved_2';
        } else {
            // Jika sudah approved_3 tapi belum ada yang approve (tidak seharusnya terjadi)
            $submission->status = 'approved_3';
        }
    }

    private function getApproverLevel($role)
    {
        return match($role) {
            'approver1' => 1,
            'approver2' => 2,
            'approver3' => 3,
            default => 0
        };
    }
}