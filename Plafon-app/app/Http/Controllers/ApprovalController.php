<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        $query = Submission::where('current_level', $level)
            ->whereIn('status', ['pending', 'approved_1', 'approved_2'])
            ->with(['sales', 'approvals.approver', 'previousSubmission']);

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

        return view('approvals.index', compact('submissions', 'level'));
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

        DB::beginTransaction();
        try {
            // Validasi khusus untuk Level 2 saat approve
            if ($level == 2 && $action === 'approved') {
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

                // Simpan data ke payment_data di tabel submissions
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
                    'note' => 'required|string|min:10'
                ], [
                    'note.required' => 'Catatan wajib diisi',
                    'note.min' => 'Catatan minimal 10 karakter'
                ]);
            }

            // Buat record approval
            $approval = new Approval();
            $approval->submission_id = $submission->id;
            $approval->approver_id = $user->id;
            $approval->level = $level;
            $approval->status = $action;
            $approval->note = $request->input('note');

            // Simpan data verifikasi Level 2 di tabel approvals juga (untuk history)
            if ($level == 2 && $action === 'approved') {
                $approval->piutang = $request->piutang;
                $approval->jml_over = $request->jml_over;
                $approval->jml_od_30 = $request->jml_od_30;
                $approval->jml_od_60 = $request->jml_od_60;
                $approval->jml_od_90 = $request->jml_od_90;
            }

            $approval->save();

            // Update status submission
            if ($action === 'approved') {
                if ($level == 3) {
                    $submission->status = 'done';
                } else {
                    $submission->status = 'approved_' . $level;
                    $submission->current_level = $level + 1;
                }
            } elseif ($action === 'rejected') {
                $submission->status = 'rejected';
                $submission->rejection_note = $request->input('note');
            } elseif ($action === 'revision') {
                $submission->status = 'revision';
                $submission->revision_note = $request->input('note');
                $submission->current_level = 1; // Kembali ke level 1
            }

            $submission->save();

            DB::commit();

            $message = match($action) {
                'approved' => 'Pengajuan berhasil disetujui',
                'rejected' => 'Pengajuan berhasil ditolak',
                'revision' => 'Pengajuan dikembalikan untuk revisi',
                default => 'Proses approval berhasil'
            };

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
            default => 0
        };
    }
}