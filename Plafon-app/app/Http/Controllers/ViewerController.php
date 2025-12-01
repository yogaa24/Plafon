<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class ViewerController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::whereIn('status', ['approved_3', 'done'])
            ->with(['sales', 'approvals.approver']);

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
        // Only show if approved
        if ($submission->status !== 'approved_3') {
            abort(403, 'Anda hanya dapat melihat pengajuan yang sudah disetujui.');
        }

        $submission->load(['sales', 'approvals.approver']);

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

    public function export(Request $request)
    {
        // Export ke Excel/CSV (opsional)
        // Implementasi export jika diperlukan
    }
}