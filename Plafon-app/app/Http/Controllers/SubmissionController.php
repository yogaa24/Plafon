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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            if (!$request->has('show_completed')) {
                $query->where('status', '!=', 'done');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%")
                ->orWhere('nama_kios', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $submissions = $query->paginate(15)->appends($request->query());

        // 🔥 Tambahkan ini untuk diperbaiki
        $customers = Submission::where('sales_id', Auth::id())
            ->where('status', 'done')
            ->get();

        return view('submissions.index', [
            'submissions' => $submissions,
            'customers' => $customers,
        ]);
    }

    public function create()
    {
        $kode = $this->generateKode();
        return view('submissions.create', compact('kode'));
    }

    /**
     * Form untuk rubah plafon customer existing
     */
    public function createRubahPlafon(Submission $submission)
    {
        // Validasi: hanya bisa rubah plafon dari submission yang sudah done
        if ($submission->status !== 'done') {
            return redirect()->route('submissions.index')
                ->with('error', 'Hanya dapat mengubah plafon dari pengajuan yang sudah selesai!');
        }

        // Validasi: hanya sales yang sama
        if ($submission->sales_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk customer ini.');
        }

        $kode = $this->generateKode();
        
        return view('submissions.create-rubah-plafon', compact('submission', 'kode'));
    }

    public function store(Request $request)
    {
        // Validasi dasar yang berlaku untuk semua jenis pengajuan
        $baseRules = [
            'nama' => 'required|string|max:255',
            'nama_kios' => 'required|string|max:255',
            'alamat' => 'required|string',
            'plafon' => 'required|integer|min:0',
            'komitmen_pembayaran' => 'required|string',
            'plafon_type' => 'required|in:open,rubah',
            'previous_submission_id' => 'nullable|exists:submissions,id',
        ];

        // Tambahkan validasi spesifik berdasarkan plafon_type
        if ($request->plafon_type === 'open') {
            $baseRules['jumlah_buka_faktur'] = 'required|integer|min:1';
        } elseif ($request->plafon_type === 'rubah') {
            $baseRules['plafon_direction'] = 'required|in:naik,turun';
            
            // Validasi tambahan: pastikan ada previous_submission_id untuk rubah plafon
            $baseRules['previous_submission_id'] = 'required|exists:submissions,id';
            
            // Validasi logika naik/turun plafon
            $request->validate([
                'plafon_direction' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->previous_submission_id) {
                            $previousSubmission = \App\Models\Submission::find($request->previous_submission_id);
                            if ($previousSubmission) {
                                $plafonBaru = $request->plafon;
                                $plafonLama = $previousSubmission->plafon;
                                
                                if ($value === 'naik' && $plafonBaru <= $plafonLama) {
                                    $fail('Plafon usulan harus lebih besar dari plafon saat ini untuk pilihan "Naik Plafon"');
                                }
                                
                                if ($value === 'turun' && $plafonBaru >= $plafonLama) {
                                    $fail('Plafon usulan harus lebih kecil dari plafon saat ini untuk pilihan "Turun Plafon"');
                                }
                            }
                        }
                    },
                ],
            ]);
        }

        $validated = $request->validate($baseRules);

        // Generate kode dan set status
        $validated['kode'] = $this->generateKode();
        $validated['sales_id'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['current_level'] = 1;

        // Untuk rubah plafon, set jumlah_buka_faktur dari submission sebelumnya
        if ($validated['plafon_type'] === 'rubah' && $validated['previous_submission_id']) {
            $previousSubmission = \App\Models\Submission::find($validated['previous_submission_id']);
            if ($previousSubmission) {
                $validated['jumlah_buka_faktur'] = $previousSubmission->jumlah_buka_faktur;
            }
        }

        Submission::create($validated);

        $message = $validated['plafon_type'] === 'open' 
            ? 'Pengajuan Open Plafon berhasil dibuat dengan kode: ' . $validated['kode']
            : 'Pengajuan Rubah Plafon berhasil dibuat dengan kode: ' . $validated['kode'];

        return redirect()->route('submissions.index')->with('success', $message);
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

        // Base validation rules
        $baseRules = [
            'nama' => 'required|string|max:255',
            'nama_kios' => 'required|string|max:255',
            'alamat' => 'required|string',
            'plafon' => 'required|integer|min:0',
            'komitmen_pembayaran' => 'required|string',
        ];

        // Add specific rules based on plafon_type
        if ($submission->plafon_type === 'open') {
            $baseRules['jumlah_buka_faktur'] = 'required|integer|min:1';
        } elseif ($submission->plafon_type === 'rubah') {
            $baseRules['plafon_direction'] = 'required|in:naik,turun';
            
            // Validate plafon direction logic
            $request->validate([
                'plafon_direction' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request, $submission) {
                        if ($submission->previousSubmission) {
                            $plafonBaru = $request->plafon;
                            $plafonLama = $submission->previousSubmission->plafon;
                            
                            if ($value === 'naik' && $plafonBaru <= $plafonLama) {
                                $fail('Plafon baru harus lebih besar dari plafon sebelumnya untuk pilihan "Naik Plafon"');
                            }
                            
                            if ($value === 'turun' && $plafonBaru >= $plafonLama) {
                                $fail('Plafon baru harus lebih kecil dari plafon sebelumnya untuk pilihan "Turun Plafon"');
                            }
                        }
                    },
                ],
            ]);
        }

        $validated = $request->validate($baseRules);

        // Reset approval status if revision
        if ($submission->status == 'revision') {
            $validated['status'] = 'pending';
            $validated['current_level'] = 1;
            $validated['revision_note'] = null;
        }

        // Update plafon_direction for rubah plafon
        if ($submission->plafon_type === 'rubah') {
            $validated['plafon_direction'] = $request->plafon_direction;
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

        $submission->load('approvals.approver', 'previousSubmission'); // ← pastikan ini ada

        $showAll = request()->get('show') == 'all';

        return view('submissions.show', compact('submission', 'showAll'));
    }

    /**
     * Form untuk open plafon dari customer existing
     */
    public function createOpenPlafon(Submission $submission)
    {
        // Validasi: hanya bisa open plafon dari submission yang sudah done
        if ($submission->status !== 'done') {
            return redirect()->route('submissions.index')
                ->with('error', 'Hanya dapat membuat open plafon dari customer yang sudah aktif!');
        }

        // Validasi: hanya sales yang sama
        if ($submission->sales_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk customer ini.');
        }

        $kode = $this->generateKode();
        
        return view('submissions.create-open-plafon', compact('submission', 'kode'));
    }

    /**
     * Ambil daftar customer yang sudah done untuk dropdown
     */
    //Fungsi data search dropdown select2
    public function getApprovedCustomers(Request $request)
    {
        $query = $request->get('q', '');
        
        $customers = Submission::where('sales_id', Auth::id())
            ->where('status', 'done')
            ->where(function($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                  ->orWhere('nama_kios', 'like', "%{$query}%");
            })
            ->select('id', 'nama', 'nama_kios', 'plafon', 'alamat')
            ->orderBy('nama')
            ->limit(20)
            ->get();

        return response()->json($customers);
    }

    private function generateKode()
    {
        $today = Carbon::today();
        $dateFormat = $today->format('dmy');
        
        $prefix = "KIUSL{$dateFormat}";
        
        $lastSubmission = Submission::where('kode', 'like', $prefix . '%')
            ->whereDate('created_at', $today)
            ->orderBy('kode', 'desc')
            ->first();
        
        if ($lastSubmission) {
            $lastNumber = (int) substr($lastSubmission->kode, -3);
            $newNumber = $lastNumber + 1;
            
            if ($newNumber > 100) {
                abort(422, 'Batas maksimal 100 pengajuan per hari telah tercapai!');
            }
        } else {
            $newNumber = 1;
        }
        
        $sequenceNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $sequenceNumber;
    }

    public function getNewKode()
    {
        return response()->json([
            'kode' => $this->generateKode()
        ]);
    }

    // //fungsi data select 2
    // public function searchNama(Request $request)
    // {
    //     try {
    //         $query = $request->get('q', '');
            
    //         if (strlen($query) < 2) {
    //             return response()->json([]);
    //         }

    //         $submissions = Submission::where('nama', 'like', "%{$query}%")
    //             ->where('status', 'done')
    //             ->with('sales:id,name')
    //             ->select('nama', 'nama_kios', 'alamat', 'plafon', 'sales_id', 'status', 'created_at')
    //             ->orderBy('created_at', 'desc')
    //             ->limit(20)
    //             ->get();

    //         $grouped = $submissions->groupBy('nama')->map(function($group) {
    //             $latest = $group->first();
    //             return [
    //                 'nama' => $latest->nama,
    //                 'nama_kios' => $latest->nama_kios,
    //                 'alamat' => $latest->alamat,
    //                 'plafon' => $latest->plafon,
    //                 'sales_name' => $latest->sales ? $latest->sales->name : 'Unknown',
    //                 'is_own' => $latest->sales_id == Auth::id(),
    //                 'status' => $latest->status,
    //                 'created_at' => $latest->created_at->format('d M Y')
    //             ];
    //         })->values();

    //         return response()->json($grouped);

    //     } catch (\Exception $e) {
    //         \Log::error('Search Nama Error: ' . $e->getMessage());
    //         return response()->json([]);
    //     }
    // }
}