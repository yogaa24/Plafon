<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::where('sales_id', Auth::id())
            ->with('approvals.approver');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
           // Default: Sembunyikan pengajuan yang sudah done
           // Kecuali jika user memilih untuk melihat yang selesai
        if (!$request->has('show_completed')) {
            $query->where('status', '!=', 'done');
        }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $submissions = $query->paginate(15)->appends($request->query());

        return view('submissions.index', compact('submissions'));
    }

    public function create()
    {
        // Generate kode otomatis untuk preview
        $kode = $this->generateKode();
        return view('submissions.create', compact('kode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_kios' => 'required|string|max:255',
            'alamat' => 'required|string',
            'plafon' => 'required|integer|min:0',
            'jumlah_buka_faktur' => 'required|integer|min:1',
            'komitmen_pembayaran' => 'required|string',
        ]);

        // Generate kode otomatis
        $validated['kode'] = $this->generateKode();
        $validated['sales_id'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['current_level'] = 1;

        Submission::create($validated);

        return redirect()->route('submissions.index')
            ->with('success', 'Pengajuan berhasil dibuat dengan kode: ' . $validated['kode']);
    }

    public function edit(Submission $submission)
    {
        if ($submission->sales_id != Auth::id()) {
            abort(403);
        }

        if (!in_array($submission->status, ['pending', 'revision'])) {
            return redirect()->route('submissions.index')
                ->with('error', 'Pengajuan tidak dapat diedit!');
        }

        return view('submissions.edit', compact('submission'));
    }

    public function update(Request $request, Submission $submission)
    {
        if ($submission->sales_id != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_kios' => 'required|string|max:255',
            'alamat' => 'required|string',
            'plafon' => 'required|integer|min:0',
            'jumlah_buka_faktur' => 'required|integer|min:1',
            'komitmen_pembayaran' => 'required|string',
        ]);

        // Reset status jika sedang revisi
        if ($submission->status == 'revision') {
            $validated['status'] = 'pending';
            $validated['current_level'] = 1;
            $validated['revision_note'] = null;
        }

        $submission->update($validated);

        return redirect()->route('submissions.index')
            ->with('success', 'Pengajuan berhasil diperbarui!');
    }

    public function destroy(Submission $submission)
    {
        if ($submission->sales_id != Auth::id()) {
            abort(403);
        }

        $submission->delete();

        return redirect()->route('submissions.index')
            ->with('success', 'Pengajuan berhasil dihapus!');
    }

    public function show(Submission $submission)
    {
        if ($submission->sales_id != Auth::id()) {
            abort(403);
        }

        $submission->load('approvals.approver');

        $showAll = request()->get('show') == 'all'; // ⬅️ tambahkan ini

        return view('submissions.show', compact('submission', 'showAll'));
    }

    /**
     * Generate kode pengajuan otomatis
     * Format: KIUSL-DDMMYY-XXX
     * Contoh: KIUSL-291125-001
     */
    private function generateKode()
    {
        $today = Carbon::today();
        $dateFormat = $today->format('dmy'); // 291125 untuk 29 November 2025
        
        // Cari pengajuan terakhir hari ini dengan prefix yang sama
        $prefix = "KIUSL{$dateFormat}";
        
        $lastSubmission = Submission::where('kode', 'like', $prefix . '%')
            ->whereDate('created_at', $today)
            ->orderBy('kode', 'desc')
            ->first();
        
        if ($lastSubmission) {
            // Ambil 3 digit terakhir dan tambah 1
            $lastNumber = (int) substr($lastSubmission->kode, -3);
            $newNumber = $lastNumber + 1;
            
            // Maksimal 100 pengajuan per hari
            if ($newNumber > 100) {
                abort(422, 'Batas maksimal 100 pengajuan per hari telah tercapai!');
            }
        } else {
            // Pengajuan pertama hari ini
            $newNumber = 1;
        }
        
        // Format 3 digit dengan leading zero: 001, 002, ..., 100
        $sequenceNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $sequenceNumber;
    }

    /**
     * API untuk mendapatkan kode baru (opsional, untuk AJAX)
     */
    public function getNewKode()
    {
        return response()->json([
            'kode' => $this->generateKode()
        ]);
    }

    /**
     * API untuk autocomplete nama dengan Select2
     * Search berdasarkan SEMUA nama yang pernah diajukan
     */
    public function searchNama(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $submissions = Submission::where('nama', 'like', "%{$query}%")
                ->where('status', 'done') // ← Hanya status done
                ->with('sales:id,name')
                ->select('nama', 'nama_kios', 'alamat', 'plafon', 'sales_id', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $grouped = $submissions->groupBy('nama')->map(function($group) {
                $latest = $group->first();
                return [
                    'nama' => $latest->nama,
                    'nama_kios' => $latest->nama_kios,
                    'alamat' => $latest->alamat,
                    'plafon' => $latest->plafon,
                    'sales_name' => $latest->sales ? $latest->sales->name : 'Unknown',
                    'is_own' => $latest->sales_id == Auth::id(),
                    'status' => $latest->status,
                    'created_at' => $latest->created_at->format('d M Y')
                ];
            })->values();

            return response()->json($grouped);

        } catch (\Exception $e) {
            \Log::error('Search Nama Error: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}