@extends('layouts.app')

@section('title', 'Open Plafon Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Open Plafon Baru</h1>
            <p class="text-sm text-gray-600 mt-1">Buat pengajuan open plafon baru untuk customer yang sudah ada</p>
        </div>
        <a href="{{ route('submissions.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
            ← Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('submissions.store') }}" method="POST" id="openPlafonForm">
            @csrf
            
            <!-- Hidden fields -->
            <input type="hidden" name="plafon_type" value="open">

            <!-- Kode (Read Only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Pengajuan <span class="text-red-500">*</span>
                </label>
                <input type="text" value="{{ $kode }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono">
                <p class="text-xs text-gray-500 mt-1">Kode akan digenerate otomatis saat pengajuan disimpan</p>
            </div>

            <!-- Hidden fields for customer info -->
            <input type="hidden" name="nama" value="{{ $submission->nama }}">
            <input type="hidden" name="nama_kios" value="{{ $submission->nama_kios }}">
            <input type="hidden" name="alamat" value="{{ $submission->alamat }}">

            <!-- Read-Only Customer Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Customer</label>
                    <input type="text" value="{{ $submission->nama }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kios</label>
                    <input type="text" value="{{ $submission->nama_kios }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea readonly rows="2" class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">{{ $submission->alamat }}</textarea>
            </div>

            <!-- Editable Fields -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Open Plafon Baru</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Plafon Baru -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plafon yang Diusulkan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="number" 
                                id="plafonBaru"
                                name="plafon" 
                                value="{{ old('plafon', $submission->plafon) }}" 
                                required 
                                min="0" 
                                step="1000"
                                class="w-full pl-12 pr-4 py-2.5 border @error('plafon') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Masukkan plafon yang diinginkan"
                                oninput="formatPlafonDisplay()">
                        </div>
                        @error('plafon')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="plafonDisplay" class="text-xs mt-1 font-medium text-blue-600 hidden">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>

                    <!-- Jumlah Buka Faktur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Buka Faktur <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="jumlah_buka_faktur" 
                            value="{{ old('jumlah_buka_faktur', $submission->jumlah_buka_faktur) }}" 
                            required 
                            min="1"
                            class="w-full px-4 py-2.5 border @error('jumlah_buka_faktur') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan jumlah buka faktur">
                        @error('jumlah_buka_faktur')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Jumlah faktur yang dapat dibuka</p>
                    </div>
                </div>

                <!-- Info Plafon Baru -->
                <div id="plafonInfo" class="mt-4 p-4 rounded-lg border bg-blue-50 border-blue-300 hidden">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="plafonInfoText" class="text-sm font-medium text-blue-800"></span>
                    </div>
                </div>

                <!-- Komitmen Pembayaran -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="komitmen_pembayaran" 
                        rows="3" 
                        required
                        class="w-full px-4 py-2.5 border @error('komitmen_pembayaran') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: Pembayaran setiap hari Senin dan Kamis">{{ old('komitmen_pembayaran', $submission->komitmen_pembayaran) }}</textarea>
                    @error('komitmen_pembayaran')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('submissions.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ajukan Open Plafon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatPlafonDisplay() {
    const plafonInput = document.getElementById('plafonBaru');
    const plafonValue = parseInt(plafonInput.value) || 0;
    const plafonDisplayEl = document.getElementById('plafonDisplay');
    const plafonInfoEl = document.getElementById('plafonInfo');
    const plafonInfoTextEl = document.getElementById('plafonInfoText');
    
    if (plafonValue === 0) {
        plafonDisplayEl.classList.add('hidden');
        plafonInfoEl.classList.add('hidden');
        return;
    }
    
    const formattedPlafon = new Intl.NumberFormat('id-ID').format(plafonValue);
    
    // Show formatted value below input
    plafonDisplayEl.classList.remove('hidden');
    plafonDisplayEl.textContent = `Rp ${formattedPlafon}`;
    
    // Show info box
    plafonInfoEl.classList.remove('hidden');
    plafonInfoTextEl.innerHTML = `Pengajuan open plafon baru sebesar <strong>Rp ${formattedPlafon}</strong>`;
}

// Validate before submit
document.getElementById('openPlafonForm').addEventListener('submit', function(e) {
    const plafonValue = parseInt(document.getElementById('plafonBaru').value) || 0;
    
    if (plafonValue <= 0) {
        e.preventDefault();
        alert('Plafon harus lebih besar dari 0');
        return;
    }
    
    // Confirmation
    const formattedPlafon = new Intl.NumberFormat('id-ID').format(plafonValue);
    
    const confirmed = confirm(
        `Konfirmasi Pengajuan Open Plafon Baru:\n\n` +
        `Plafon Baru: Rp ${formattedPlafon}\n\n` +
        `Lanjutkan pengajuan?`
    );
    
    if (!confirmed) {
        e.preventDefault();
    }
});

// Initialize on load if old values exist
document.addEventListener('DOMContentLoaded', function() {
    const plafonInput = document.getElementById('plafonBaru');
    if (plafonInput.value) {
        formatPlafonDisplay();
    }
});
</script>
@endsection