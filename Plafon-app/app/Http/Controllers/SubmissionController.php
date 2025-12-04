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
        // Validasi dasar
        $rules = [
            'nama' => 'required|string|max:255',
            'nama_kios' => 'required|string|max:255',
            'alamat' => 'required|string',
            'plafon' => 'required|numeric|min:0',
            'plafon_type' => 'required|in:open,rubah',
            'plafon_direction' => 'nullable|in:naik,turun',
            'previous_submission_id' => 'nullable|exists:submissions,id',
            'komitmen_pembayaran' => 'required|string',
            'payment_type' => 'nullable|in:od,over',
            // Validasi untuk nilai-nilai payment
            'od_piutang_value' => 'nullable|numeric|min:0',
            'od_jml_over_value' => 'nullable|numeric|min:0',
            'od_30_value' => 'nullable|numeric|min:0',
            'od_60_value' => 'nullable|numeric|min:0',
            'od_90_value' => 'nullable|numeric|min:0',
            'over_piutang_value' => 'nullable|numeric|min:0',
            'over_jml_over_value' => 'nullable|numeric|min:0',
            'over_od_30_value' => 'nullable|numeric|min:0',
            'over_od_60_value' => 'nullable|numeric|min:0',
            'over_od_90_value' => 'nullable|numeric|min:0',
        ];

        // Tambahkan validasi spesifik berdasarkan plafon_type
        if ($request->plafon_type === 'open') {
            $rules['jumlah_buka_faktur'] = 'required|integer|min:1';
        } elseif ($request->plafon_type === 'rubah') {
            $rules['plafon_direction'] = 'required|in:naik,turun';
            $rules['previous_submission_id'] = 'required|exists:submissions,id';
        }

        $validated = $request->validate($rules);

        // Validasi logika naik/turun untuk rubah plafon
        if ($request->plafon_type === 'rubah' && $request->previous_submission_id) {
            $previousSubmission = Submission::find($request->previous_submission_id);
            
            if ($previousSubmission) {
                $plafonBaru = $request->plafon;
                $plafonLama = $previousSubmission->plafon;
                
                if ($request->plafon_direction === 'naik' && $plafonBaru <= $plafonLama) {
                    return back()->withErrors([
                        'plafon' => 'Plafon usulan harus lebih besar dari plafon saat ini untuk pilihan "Naik Plafon"'
                    ])->withInput();
                }
                
                if ($request->plafon_direction === 'turun' && $plafonBaru >= $plafonLama) {
                    return back()->withErrors([
                        'plafon' => 'Plafon usulan harus lebih kecil dari plafon saat ini untuk pilihan "Turun Plafon"'
                    ])->withInput();
                }
            }
        }

        // Generate kode
        $kode = $this->generateKode($validated['plafon_type']);

        // Prepare payment data
        $paymentData = null;
        if ($request->payment_type) {
            $paymentData = [];
            
            if ($request->payment_type === 'od') {
                if ($request->od_piutang_value) $paymentData['piutang'] = $request->od_piutang_value;
                if ($request->od_jml_over_value) $paymentData['jml_over'] = $request->od_jml_over_value;
                if ($request->od_30_value) $paymentData['od_30'] = $request->od_30_value;
                if ($request->od_60_value) $paymentData['od_60'] = $request->od_60_value;
                if ($request->od_90_value) $paymentData['od_90'] = $request->od_90_value;
            } elseif ($request->payment_type === 'over') {
                if ($request->over_piutang_value) $paymentData['piutang'] = $request->over_piutang_value;
                if ($request->over_jml_over_value) $paymentData['jml_over'] = $request->over_jml_over_value;
                if ($request->over_od_30_value) $paymentData['od_30'] = $request->over_od_30_value;
                if ($request->over_od_60_value) $paymentData['od_60'] = $request->over_od_60_value;
                if ($request->over_od_90_value) $paymentData['od_90'] = $request->over_od_90_value;
            }
        }

        // Tentukan jumlah_buka_faktur
        $jumlahBukaFaktur = null;
        if ($validated['plafon_type'] === 'open') {
            $jumlahBukaFaktur = $validated['jumlah_buka_faktur'];
        } elseif ($validated['plafon_type'] === 'rubah' && isset($validated['previous_submission_id'])) {
            // Untuk rubah plafon, ambil dari submission sebelumnya
            $previousSubmission = Submission::find($validated['previous_submission_id']);
            if ($previousSubmission) {
                $jumlahBukaFaktur = $previousSubmission->jumlah_buka_faktur;
            }
        }

        // Create submission
        $submission = Submission::create([
            'kode' => $kode,
            'nama' => $validated['nama'],
            'nama_kios' => $validated['nama_kios'],
            'alamat' => $validated['alamat'],
            'plafon' => $validated['plafon'],
            'plafon_type' => $validated['plafon_type'],
            'plafon_direction' => $validated['plafon_direction'] ?? null,
            'previous_submission_id' => $validated['previous_submission_id'] ?? null,
            'jumlah_buka_faktur' => $jumlahBukaFaktur,
            'komitmen_pembayaran' => $validated['komitmen_pembayaran'],
            'payment_type' => $request->payment_type,
            'payment_data' => $paymentData,
            'sales_id' => auth()->id(),
            'status' => 'pending',
            'current_level' => 1,
        ]);

        $message = $validated['plafon_type'] === 'open' 
            ? 'Pengajuan Open Plafon berhasil dibuat dengan kode: ' . $kode
            : 'Pengajuan Rubah Plafon berhasil dibuat dengan kode: ' . $kode;

        return redirect()->route('submissions.index')
            ->with('success', $message);
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
}