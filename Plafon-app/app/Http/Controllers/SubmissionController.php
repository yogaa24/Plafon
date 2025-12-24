<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $hasFilter = $request->filled('view') || $request->filled('status') || $request->filled('search');

        // PERUBAHAN 1: Tambahkan paginate(15) untuk customers
        if ($request->filled('customer_search')) {
            $search = $request->customer_search;
            $customers = Customer::where('sales_id', $user->id)
                ->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nama_kios', 'like', "%{$search}%")
                    ->orWhere('kode_customer', 'like', "%{$search}%");
                })
                ->orderBy('nama')
                ->paginate(15)  // ← TAMBAHKAN INI
                ->appends($request->except('page'));  // ← DAN INI untuk maintain query params
        } else {
            $customers = Customer::where('sales_id', $user->id)
                ->orderBy('nama')
                ->paginate(15)  // ← TAMBAHKAN INI
                ->appends($request->except('page'));  // ← DAN INI
        }

        // Submissions query (sudah ada paginate)
        if ($hasFilter) {
            $query = Submission::where('sales_id', $user->id);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('nama_kios', 'like', "%{$search}%");
                });
            }

            $submissions = $query->with(['customer', 'approvals'])
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->appends($request->except('page'));
        } else {
            $submissions = collect();
        }

        return view('submissions.index', compact('customers', 'submissions', 'hasFilter'));
    }

    public function create()
    {
        $kode = $this->generateKode();
        return view('submissions.create', compact('kode'));
    }

    /**
     * Form untuk rubah plafon customer existing
     */
    public function createRubahPlafon(Customer $customer)
    {
        // Validasi: customer harus aktif
        if ($customer->status !== 'active') {
            return redirect()->route('submissions.index')
                ->with('error', 'Customer tidak aktif!');
        }

        // Validasi: hanya sales yang handle customer ini yang bisa akses
        if ($customer->sales_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk customer ini.');
        }

        // Cek apakah ada pengajuan yang sedang berjalan
        if ($customer->hasPendingSubmission()) {
            return redirect()->route('submissions.index')
                ->with('error', 'Customer ini masih memiliki pengajuan yang sedang diproses!');
        }

        $kode = $this->generateKode();
        
        return view('submissions.create-rubah-plafon', compact('customer', 'kode'));
    }

    /**
     * Form untuk open plafon dari customer existing
     */
    public function createOpenPlafon(Customer $customer)
    {
        // Validasi: customer harus aktif
        if ($customer->status !== 'active') {
            return redirect()->route('submissions.index')
                ->with('error', 'Customer tidak aktif!');
        }

        // Validasi: hanya sales yang handle customer ini yang bisa akses
        if ($customer->sales_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk customer ini.');
        }

        // Cek apakah ada pengajuan yang sedang berjalan
        if ($customer->hasPendingSubmission()) {
            return redirect()->route('submissions.index')
                ->with('error', 'Customer ini masih memiliki pengajuan yang sedang diproses!');
        }

        $kode = $this->generateKode();
        
        return view('submissions.create-open-plafon', compact('customer', 'kode'));
    }

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

    public function store(Request $request)
    {
        // Validasi dasar
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'plafon' => 'required|numeric|min:0',
            'plafon_type' => 'required|in:open,rubah',
            'plafon_direction' => 'nullable|in:naik,turun',
            'previous_submission_id' => 'nullable|exists:submissions,id',
            'komitmen_pembayaran' => 'required|string',
            'payment_type' => 'nullable|in:od,over',
            'od_piutang_value' => 'nullable|numeric',
            'od_jml_over_value' => 'nullable|numeric',
            'od_30_value' => 'nullable|numeric',
            'od_60_value' => 'nullable|numeric',
            'od_90_value' => 'nullable|numeric',
            'over_piutang_value' => 'nullable|numeric',
            'over_jml_over_value' => 'nullable|numeric',
            'over_od_30_value' => 'nullable|numeric',
            'over_od_60_value' => 'nullable|numeric',
            'over_od_90_value' => 'nullable|numeric',
            // TAMBAHKAN INI
            'lampiran' => 'nullable|array|max:3',
            'lampiran.*' => 'image|mimes:jpeg,jpg,png|max:10240',
        ];

        // Tambahkan validasi spesifik berdasarkan plafon_type
        if ($request->plafon_type === 'open') {
            // PERUBAHAN: Hilangkan batasan max plafon_aktif
            $rules['jumlah_buka_faktur'] = 'required|integer|min:1';
        } elseif ($request->plafon_type === 'rubah') {
            $rules['plafon_direction'] = 'required|in:naik,turun';
        }

        $validated = $request->validate($rules);

        // Ambil data customer
        $customer = Customer::findOrFail($validated['customer_id']);

        // Validasi logika naik/turun untuk rubah plafon
        if ($request->plafon_type === 'rubah') {
            $plafonBaru = $request->plafon;
            $plafonLama = $customer->plafon_aktif;
            
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

        // Generate kode
        $kode = $this->generateKode();

        // Prepare payment data
        $paymentData = null;
        if ($request->payment_type) {
            $paymentData = [];
            
            if ($request->payment_type === 'od') {
                if ($request->filled('od_piutang_value')) $paymentData['piutang'] = $request->od_piutang_value;
                if ($request->filled('od_jml_over_value')) $paymentData['jml_over'] = $request->od_jml_over_value;
                if ($request->filled('od_30_value')) $paymentData['od_30'] = $request->od_30_value;
                if ($request->filled('od_60_value')) $paymentData['od_60'] = $request->od_60_value;
                if ($request->filled('od_90_value')) $paymentData['od_90'] = $request->od_90_value;
            } elseif ($request->payment_type === 'over') {
                if ($request->filled('over_piutang_value')) $paymentData['piutang'] = $request->over_piutang_value;
                if ($request->filled('over_jml_over_value')) $paymentData['jml_over'] = $request->over_jml_over_value;
                if ($request->filled('over_od_30_value')) $paymentData['od_30'] = $request->over_od_30_value;
                if ($request->filled('over_od_60_value')) $paymentData['od_60'] = $request->over_od_60_value;
                if ($request->filled('over_od_90_value')) $paymentData['od_90'] = $request->over_od_90_value;
            }
        }

        // Tentukan jumlah_buka_faktur
        $jumlahBukaFaktur = null;
        if ($validated['plafon_type'] === 'open') {
            $jumlahBukaFaktur = $validated['jumlah_buka_faktur'];
        } elseif ($validated['plafon_type'] === 'rubah') {
            // Untuk rubah plafon, ambil dari submission sebelumnya (yang terakhir done)
            $latestApproved = $customer->latestApprovedSubmission;
            if ($latestApproved) {
                $jumlahBukaFaktur = $latestApproved->jumlah_buka_faktur;
            }
        }

        // Cari previous submission (untuk rubah plafon)
        $previousSubmissionId = null;
        if ($validated['plafon_type'] === 'rubah') {
            $latestApproved = $customer->latestApprovedSubmission;
            if ($latestApproved) {
                $previousSubmissionId = $latestApproved->id;
            }
        }

        $lampiranPaths = null;
        if ($request->hasFile('lampiran')) {
            // Buat folder jika belum ada
            $uploadPath = public_path('lampiran');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $uploadedPaths = [];
            foreach ($request->file('lampiran') as $file) {
                // Compress gambar
                $compressedImage = $this->compressImage($file);
                
                // Generate unique filename
                $filename = 'lampiran-' . time() . uniqid() . '.jpg';
                file_put_contents($uploadPath . '/' . $filename, $compressedImage);
                $uploadedPaths[] = 'lampiran/' . $filename;
            }
            
            $lampiranPaths = json_encode($uploadedPaths);
        }

        // Create submission
        $submission = Submission::create([
            'kode' => $kode,
            'customer_id' => $customer->id,
            'nama' => $customer->nama,
            'nama_kios' => $customer->nama_kios,
            'alamat' => $customer->alamat,
            'plafon' => $validated['plafon'],
            'plafon_type' => $validated['plafon_type'],
            'plafon_direction' => $validated['plafon_direction'] ?? null,
            'previous_submission_id' => $previousSubmissionId,
            'jumlah_buka_faktur' => $jumlahBukaFaktur,
            'komitmen_pembayaran' => $validated['komitmen_pembayaran'],
            'payment_type' => $request->payment_type,
            'payment_data' => $paymentData,
            'sales_id' => auth()->id(),
            'status' => 'pending',
            'current_level' => 1,
            'lampiran_path' => $lampiranPaths, // TAMBAHKAN INI
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

        if (!in_array($submission->status, ['pending'])) {
            return redirect()->route('submissions.index')
                ->with('error', 'Pengajuan tidak dapat diedit!');
        }

        // TAMBAHKAN INI - Decode lampiran path
        $lampiranPaths = null;
        if ($submission->lampiran_path) {
            $lampiranPaths = json_decode($submission->lampiran_path, true);
        }

        return view('submissions.edit', compact('submission', 'lampiranPaths'));
    }

    public function update(Request $request, Submission $submission)
    {
        if ($submission->sales_id != Auth::id()) {
            abort(403);
        }
    
        // Base validation rules (hapus nama, nama_kios, alamat, plafon dari validasi)
        $baseRules = [
            'komitmen_pembayaran' => 'required|string',
            'payment_type' => 'nullable|in:od,over',
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
            'lampiran' => 'nullable|array|max:3',
            'lampiran.*' => 'image|mimes:jpeg,jpg,png|max:10240',
            'delete_lampiran' => 'nullable|array',
            'delete_lampiran.*' => 'string',
        ];
    
        // Add specific rules based on plafon_type
        if ($submission->plafon_type === 'open') {
            $baseRules['jumlah_buka_faktur'] = 'required|integer|min:1';
        } elseif ($submission->plafon_type === 'rubah') {
            $baseRules['plafon_direction'] = 'required|in:naik,turun';
            $baseRules['plafon'] = 'required|numeric|min:0'; // Plafon tetap bisa diubah untuk rubah plafon
            
            // Validate plafon direction logic
            $request->validate([
                'plafon_direction' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request, $submission) {
                        if ($submission->customer) {
                            $plafonBaru = $request->plafon;
                            $plafonLama = $submission->customer->plafon_aktif;
                            
                            if ($value === 'naik' && $plafonBaru <= $plafonLama) {
                                $fail('Plafon baru harus lebih besar dari plafon saat ini untuk pilihan "Naik Plafon"');
                            }
                            
                            if ($value === 'turun' && $plafonBaru >= $plafonLama) {
                                $fail('Plafon baru harus lebih kecil dari plafon saat ini untuk pilihan "Turun Plafon"');
                            }
                        }
                    },
                ],
            ]);
        }
    
        $validated = $request->validate($baseRules);
    
        // TAMBAHKAN: Handle Delete Lampiran
        $currentLampiran = json_decode($submission->lampiran_path, true) ?? [];
        
        if ($request->has('delete_lampiran')) {
            foreach ($request->delete_lampiran as $pathToDelete) {
                // Hapus file fisik
                $fullPath = public_path($pathToDelete);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                
                // Hapus dari array
                $currentLampiran = array_filter($currentLampiran, function($path) use ($pathToDelete) {
                    return $path !== $pathToDelete;
                });
            }
            $currentLampiran = array_values($currentLampiran); // Re-index array
        }

        // TAMBAHKAN: Handle Upload Lampiran Baru
        if ($request->hasFile('lampiran')) {
            $uploadPath = public_path('lampiran');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            foreach ($request->file('lampiran') as $file) {
                // Cek apakah total lampiran tidak melebihi 3
                if (count($currentLampiran) >= 3) {
                    break;
                }
                
                // Compress gambar
                $compressedImage = $this->compressImage($file);
                
                // Generate unique filename
                $filename = 'lampiran-' . time() . uniqid() . '.jpg';
                file_put_contents($uploadPath . '/' . $filename, $compressedImage);
                $currentLampiran[] = 'lampiran/' . $filename;
            }
        }

        // Update lampiran_path
        if (!empty($currentLampiran)) {
            $validated['lampiran_path'] = json_encode($currentLampiran);
        } else {
            $validated['lampiran_path'] = null;
        }

        // Update payment data jika ada
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

            $validated['payment_type'] = $request->payment_type;
            $validated['payment_data'] = $paymentData;
        } else {
            $validated['payment_type'] = null;
            $validated['payment_data'] = null;
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

        $submission->load('approvals.approver', 'previousSubmission', 'customer');

        $showAll = request()->get('show') == 'all';

        return view('submissions.show', compact('submission', 'showAll'));
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