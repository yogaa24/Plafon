<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $level = $this->getApproverLevel($user->role);

        $query = Submission::where('current_level', $level)
            ->whereIn('status', ['pending', 'approved_1', 'approved_2'])
            ->with('sales', 'approvals.approver');

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

        if ($submission->current_level != $level) {
            return redirect()->route('approvals.index')
                ->with('error', 'Pengajuan tidak dalam level approval Anda!');
        }

        $validated = $request->validate([
            'action' => 'required|in:approved,rejected,revision',
            'note' => 'required_if:action,rejected,revision|nullable|string'
        ]);

        Approval::create([
            'submission_id' => $submission->id,
            'approver_id' => $user->id,
            'level' => $level,
            'status' => $validated['action'],
            'note' => $validated['note'] ?? null
        ]);

        if ($validated['action'] == 'approved') {
            if ($level == 3) {
                $submission->update([
                    'status' => 'approved_3'
                ]);
            } else {
                $submission->update([
                    'status' => 'approved_' . $level,
                    'current_level' => $level + 1
                ]);
            }
            $message = 'Pengajuan berhasil disetujui!';
        } elseif ($validated['action'] == 'rejected') {
            $submission->update([
                'status' => 'rejected',
                'rejection_note' => $validated['note']
            ]);
            $message = 'Pengajuan berhasil ditolak!';
        } else {
            $submission->update([
                'status' => 'revision',
                'revision_note' => $validated['note'],
                'current_level' => 1
            ]);
            $message = 'Pengajuan dikembalikan untuk revisi!';
        }

        return redirect()->route('approvals.index')
            ->with('success', $message);
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