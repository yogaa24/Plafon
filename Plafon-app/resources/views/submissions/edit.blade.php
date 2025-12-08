@extends('layouts.app')

@section('title', 'Edit Pengajuan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('submissions.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r {{ $submission->plafon_type === 'open' ? 'from-blue-600 to-blue-700' : 'from-purple-600 to-purple-700' }} px-6 py-4">
            <h1 class="text-2xl font-bold text-white">
                Edit Pengajuan {{ $submission->plafon_type === 'open' ? 'Open' : 'Rubah' }} Plafon
            </h1>
            <p class="text-blue-100 text-sm mt-1">Perbarui informasi pengajuan penjualan</p>
        </div>

        <form action="{{ route('submissions.update', $submission) }}" method="POST" class="p-6" id="editForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Hidden Fields -->
            <input type="hidden" name="plafon_type" value="{{ $submission->plafon_type }}">
            @if($submission->plafon_type === 'rubah')
                <input type="hidden" name="previous_submission_id" value="{{ $submission->previous_submission_id }}">
            @endif

            <!-- Kode (Read-only) -->
            <div class="mb-6 bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                <div class="flex items-start">
                    <svg class="w-4 h-4 text-gray-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    <div>
                        <p class="text-sm font-sm text-gray-700">Kode Pengajuan</p>
                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $submission->kode }}</p>
                        <p class="text-xs text-gray-600 mt-1">Kode tidak dapat diubah</p>
                    </div>
                </div>
            </div>

            <!-- Jenis Pengajuan Badge -->
            <div class="mb-6">
                @if($submission->plafon_type === 'open')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Open Plafon
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Rubah Plafon
                    </span>
                @endif
            </div>

            @if($submission->revision_note)
            <div class="mb-6 bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                <div class="flex">
                    <svg class="w-5 h-5 text-orange-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-orange-800">Catatan Revisi:</p>
                        <p class="text-sm text-orange-700 mt-1">{{ $submission->revision_note }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama (Read-Only) -->
                <div>
                    <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama
                    </label>
                    <input type="text" value="{{ $submission->nama }}" readonly
                        class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">Data tidak dapat diubah</p>
                </div>

                <!-- Nama Kios (Read-Only) -->
                <div>
                    <label for="nama_kios" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Kios
                    </label>
                    <input type="text" value="{{ $submission->nama_kios }}" readonly
                        class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                    <p class="text-xs text-gray-500 mt-1">Data tidak dapat diubah</p>
                </div>

                <!-- Alamat (Read-Only) -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat
                    </label>
                    <textarea rows="3" readonly
                        class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">{{ $submission->alamat }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Data tidak dapat diubah</p>
                </div>

                @if($submission->plafon_type === 'rubah')
                    <!-- FORM RUBAH PLAFON -->
                    
                    <!-- Jenis Perubahan Plafon -->
                    <div class="md:col-span-2">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                Jenis Perubahan Plafon <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-6">
                                <label class="flex items-center cursor-pointer group">
                                    <input 
                                        type="radio" 
                                        name="plafon_direction" 
                                        value="naik" 
                                        {{ old('plafon_direction', $submission->plafon_direction) == 'naik' ? 'checked' : '' }}
                                        required
                                        class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500">
                                    <span class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                        </svg>
                                        <span class="font-medium text-gray-700 group-hover:text-green-600">Naik Plafon</span>
                                    </span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input 
                                        type="radio" 
                                        name="plafon_direction" 
                                        value="turun" 
                                        {{ old('plafon_direction', $submission->plafon_direction) == 'turun' ? 'checked' : '' }}
                                        required
                                        class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-500">
                                    <span class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                        </svg>
                                        <span class="font-medium text-gray-700 group-hover:text-red-600">Turun Plafon</span>
                                    </span>
                                </label>
                            </div>
                            @error('plafon_direction')
                                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Plafon Sebelumnya (Read-only) -->
                    @if($submission->customer)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Plafon Sebelumnya (Customer)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="text" 
                                value="{{ number_format($submission->customer->plafon_aktif, 0, ',', '.') }}" 
                                readonly 
                                class="w-full pl-12 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-semibold">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Plafon aktif saat ini di master customer</p>
                    </div>
                    @endif

                    <!-- Plafon Baru -->
                    <div>
                        <label for="plafon" class="block text-sm font-semibold text-gray-700 mb-2">
                            Plafon Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="number" 
                                name="plafon" 
                                id="plafon" 
                                value="{{ old('plafon', $submission->plafon) }}" 
                                min="0" 
                                step="1000"
                                required
                                class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('plafon') border-red-500 @enderror">
                        </div>
                        @error('plafon')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @else
                    <!-- FORM OPEN PLAFON -->
                    
                    <!-- Plafon (Read-Only untuk Open) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Plafon
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="text" 
                                value="{{ number_format($submission->plafon, 0, ',', '.') }}" 
                                readonly 
                                class="w-full pl-12 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-semibold">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Plafon tidak dapat diubah</p>
                    </div>

                    <!-- Jumlah Buka Faktur -->
                    <div>
                        <label for="jumlah_buka_faktur" class="block text-sm font-semibold text-gray-700 mb-2">
                            Jumlah Buka Faktur <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="jumlah_buka_faktur" id="jumlah_buka_faktur" value="{{ old('jumlah_buka_faktur', $submission->jumlah_buka_faktur) }}" min="1" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jumlah_buka_faktur') border-red-500 @enderror">
                        @error('jumlah_buka_faktur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Komitmen Pembayaran -->
                <div class="md:col-span-2">
                    <label for="komitmen_pembayaran" class="block text-sm font-semibold text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <textarea name="komitmen_pembayaran" id="komitmen_pembayaran" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('komitmen_pembayaran') border-red-500 @enderror">{{ old('komitmen_pembayaran', $submission->komitmen_pembayaran) }}</textarea>
                    @error('komitmen_pembayaran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Pembayaran Section (hanya untuk Open Plafon) -->
                @if($submission->plafon_type === 'open')
                <div class="md:col-span-2 mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Jenis Pembayaran</h4>
                    
                    <!-- Radio Buttons: OD or Over -->
                    <div class="flex gap-8 mb-4">
                        <div class="flex items-center">
                            <input 
                                type="radio" 
                                id="type_od" 
                                name="payment_type" 
                                value="od"
                                {{ old('payment_type', $submission->payment_type) == 'od' ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                onchange="togglePaymentType()">
                            <label for="type_od" class="ml-2 text-sm text-gray-700 cursor-pointer">OD</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input 
                                type="radio" 
                                id="type_over" 
                                name="payment_type" 
                                value="over"
                                {{ old('payment_type', $submission->payment_type) == 'over' ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                onchange="togglePaymentType()">
                            <label for="type_over" class="ml-2 text-sm text-gray-700 cursor-pointer">Over</label>
                        </div>
                    </div>

                    @php
                        $paymentData = is_array($submission->payment_data) 
                            ? $submission->payment_data 
                            : json_decode($submission->payment_data, true) ?? [];
                    @endphp

                    <div class="border-t pt-4">
                        <!-- OD Section -->
                        <div id="odSection" class="space-y-3 {{ old('payment_type', $submission->payment_type) != 'od' ? 'hidden' : '' }}">
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Piutang</label>
                                <input type="number" name="od_piutang_value" min="0"
                                    value="{{ old('od_piutang_value', $paymentData['piutang'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml Over</label>
                                <input type="number" name="od_jml_over_value" min="0"
                                    value="{{ old('od_jml_over_value', $paymentData['jml_over'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 30</label>
                                <input type="number" name="od_30_value" min="0"
                                    value="{{ old('od_30_value', $paymentData['od_30'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 60</label>
                                <input type="number" name="od_60_value" min="0"
                                    value="{{ old('od_60_value', $paymentData['od_60'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 90</label>
                                <input type="number" name="od_90_value" min="0"
                                    value="{{ old('od_90_value', $paymentData['od_90'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Over Section -->
                        <div id="overSection" class="space-y-3 {{ old('payment_type', $submission->payment_type) != 'over' ? 'hidden' : '' }}">
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Piutang</label>
                                <input type="number" name="over_piutang_value" min="0"
                                    value="{{ old('over_piutang_value', $paymentData['piutang'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml Over</label>
                                <input type="number" name="over_jml_over_value" min="0"
                                    value="{{ old('over_jml_over_value', $paymentData['jml_over'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 30</label>
                                <input type="number" name="over_od_30_value" min="0"
                                    value="{{ old('over_od_30_value', $paymentData['od_30'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 60</label>
                                <input type="number" name="over_od_60_value" min="0"
                                    value="{{ old('over_od_60_value', $paymentData['od_60'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 90</label>
                                <input type="number" name="over_od_90_value" min="0"
                                    value="{{ old('over_od_90_value', $paymentData['od_90'] ?? '') }}"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Keterangan -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan (Opsional)
                </label>
                <textarea 
                    name="keterangan" 
                    rows="3" 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan', $submission->keterangan) }}</textarea>
            </div>

            <!-- Upload Lampiran -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Lampiran Gambar (Opsional)
                </label>
                
                @if($submission->lampiran_path)
                <div class="mb-3">
                    <p class="text-sm text-gray-600 mb-2">Lampiran saat ini:</p>
                    <div class="relative inline-block">
                        <img src="{{ Storage::url($submission->lampiran_path) }}" 
                            alt="Lampiran" 
                            class="w-60 max-h-60 object-contain rounded-lg border border-gray-300">
                        <label class="flex items-center mt-2 text-sm text-gray-600">
                            <input type="checkbox" name="hapus_lampiran" value="1" class="mr-2">
                            Hapus lampiran ini
                        </label>
                    </div>
                </div>
                @endif
                
                <input 
                    type="file" 
                    name="lampiran" 
                    accept="image/*"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    onchange="previewImage(event)">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, JPEG (Max: 2MB) - Upload file baru untuk mengganti</p>
                
                <!-- Image Preview -->
                <div id="imagePreview" class="mt-3 hidden">
                    <p class="text-sm text-gray-600 mb-2">Preview gambar baru:</p>
                    <img id="preview" class="w-60 max-h-60 object-contain rounded-lg border border-gray-300" alt="Preview">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('submissions.index') }}" 
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 {{ $submission->plafon_type === 'open' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition">
                    Update Pengajuan
                </button>
            </div>
        </form>
    </div>

    @if($submission->status == 'revision')
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-yellow-800">
                <p class="font-semibold mb-1">Informasi:</p>
                <p class="text-yellow-700">Setelah update, pengajuan akan kembali ke status "Pending" dan approval akan dimulai dari awal (Level 1).</p>
            </div>
        </div>
    </div>
    @endif
</div>

@if($submission->plafon_type === 'open')
<script>
function togglePaymentType() {
    const od = document.getElementById('type_od').checked;
    const over = document.getElementById('type_over').checked;

    document.getElementById('odSection').classList.toggle('hidden', !od);
    document.getElementById('overSection').classList.toggle('hidden', !over);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentType();
});
</script>
@endif
@endsection