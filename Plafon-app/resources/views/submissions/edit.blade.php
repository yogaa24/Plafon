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
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Edit Pengajuan</h1>
            <p class="text-blue-100 text-sm mt-1">Perbarui informasi pengajuan penjualan</p>
        </div>

        <form action="{{ route('submissions.update', $submission) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Kode (Read-only) -->
            <div class="mb-6 bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-gray-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Kode Pengajuan</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $submission->kode }}</p>
                        <p class="text-xs text-gray-600 mt-1">Kode tidak dapat diubah</p>
                    </div>
                </div>
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
                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $submission->nama) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Kios -->
                <div>
                    <label for="nama_kios" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Kios <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kios" id="nama_kios" value="{{ old('nama_kios', $submission->nama_kios) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama_kios') border-red-500 @enderror">
                    @error('nama_kios')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alamat" id="alamat" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('alamat') border-red-500 @enderror">{{ old('alamat', $submission->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plafon -->
                <div>
                    <label for="plafon" class="block text-sm font-semibold text-gray-700 mb-2">
                        Plafon (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="plafon" id="plafon" value="{{ old('plafon', (int)$submission->plafon) }}" min="0" step="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('plafon') border-red-500 @enderror">
                    @error('plafon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan nilai dalam rupiah tanpa titik/koma. Contoh: 5000000 untuk Rp 5.000.000</p>
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
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('submissions.index') }}" 
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition">
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
@endsection