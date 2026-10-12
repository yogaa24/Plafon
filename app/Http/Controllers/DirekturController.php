<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\User;
use App\Exports\Level3DoneExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DirekturController extends Controller
{
    /**
     * Tampilan Pemantauan Seluruh Proses Bisnis untuk Direktur
     */
    public function index(Request $request)
    {
        $query = Submission::with(['sales', 'customer', 'approvals.approver']);

        // Search: Kode, Nama, Nama Kios, Sales, Kode Customer
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%")
                  ->orWhereHas('sales', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        // Filter Status / Tahapan Proses
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'in_progress') {
                $query->whereIn('status', ['pending', 'approved_1', 'approved_2', 'approved_3', 'approved_4', 'approved_5', 'pending_viewer']);
            } elseif ($request->status === 'direksi') {
                $query->whereIn('status', ['approved_4', 'approved_5']);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter Jenis Plafon
        if ($request->filled('plafon_type') && in_array($request->plafon_type, ['open', 'rubah'])) {
            $query->where('plafon_type', $request->plafon_type);
        }

        // Filter Arah Plafon (Naik / Turun)
        if ($request->filled('plafon_direction') && in_array($request->plafon_direction, ['naik', 'turun'])) {
            $query->where('plafon_direction', $request->plafon_direction);
        }

        // Filter Status Target
        if ($request->filled('target_status') && in_array($request->target_status, ['masuk_target', 'tidak_masuk_target'])) {
            $query->where('target_status', $request->target_status);
        }

        // Filter Sales
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Statistik Proses Bisnis Keseluruhan (Ringkasan Eksekutif Direktur)
        $stats = [
            'total'               => Submission::count(),
            'in_progress'         => Submission::whereIn('status', ['pending', 'approved_1', 'approved_2', 'approved_3', 'approved_4', 'approved_5', 'pending_viewer'])->count(),
            'done'                => Submission::where('status', 'done')->count(),
            'rejected'            => Submission::where('status', 'rejected')->count(),
            'revision'            => Submission::where('status', 'revision')->count(),
            
            // Per Tahapan Workflow
            'stage_sc'            => Submission::where('status', 'pending')->count(),
            'stage_tc'            => Submission::where('status', 'approved_1')->count(),
            'stage_keuangan'      => Submission::where('status', 'approved_2')->count(),
            'stage_kadep'         => Submission::where('status', 'approved_3')->count(),
            'stage_direksi'       => Submission::whereIn('status', ['approved_4', 'approved_5'])->count(),
            'stage_input'         => Submission::where('status', 'pending_viewer')->count(),

            // Metrik Nilai
            'total_nominal_plafon'=> Submission::sum('plafon'),
            'done_nominal_plafon' => Submission::where('status', 'done')->sum('plafon'),
            'masuk_target_count'  => Submission::where('target_status', 'masuk_target')->count(),
            'tidak_masuk_target_count' => Submission::where('target_status', 'tidak_masuk_target')->count(),
        ];

        // Sort & Pagination
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $submissions = $query->orderBy($sortBy, $sortOrder)->paginate(15)->appends($request->query());

        // List Sales untuk Dropdown
        $salesList = User::where('role', 'sales')->orderBy('name')->get();

        return view('direktur.index', compact('submissions', 'salesList', 'stats'));
    }

    /**
     * Detail Lengkap Pengajuan untuk Direktur
     */
    public function show(Submission $submission)
    {
        $submission->load(['sales', 'customer', 'approvals.approver']);
        return view('direktur.show', compact('submission'));
    }

    /**
     * Export Data Pemantauan Proses Bisnis ke Excel
     */
    public function export(Request $request)
    {
        $query = Submission::with(['sales', 'customer', 'approvals.approver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%")
                  ->orWhereHas('sales', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'in_progress') {
                $query->whereIn('status', ['pending', 'approved_1', 'approved_2', 'approved_3', 'approved_4', 'approved_5', 'pending_viewer']);
            } elseif ($request->status === 'direksi') {
                $query->whereIn('status', ['approved_4', 'approved_5']);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('plafon_type') && in_array($request->plafon_type, ['open', 'rubah'])) {
            $query->where('plafon_type', $request->plafon_type);
        }

        if ($request->filled('target_status') && in_array($request->target_status, ['masuk_target', 'tidak_masuk_target'])) {
            $query->where('target_status', $request->target_status);
        }

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
        $filename = 'Pemantauan Proses Bisnis Direktur - ' . date('Y-m-d') . '.xlsx';

        return Excel::download(new Level3DoneExport($submissions), $filename);
    }
}
