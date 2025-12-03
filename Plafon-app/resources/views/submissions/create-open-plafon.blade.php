@extends('layouts.app')

@section('title', 'Open Plafon Baru')

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
            <h1 class="text-2xl font-bold text-white">Open Plafon Baru</h1>
            <p class="text-blue-100 text-sm mt-1">Buat pengajuan open plafon baru untuk customer: <strong>{{ $submission->nama }}</strong></p>
        </div>

        <!-- Info Customer Sebelumnya -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 m-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-blue-900">Data Customer Existing</h4>
                    <div class="mt-2 text-sm text-blue-800 space-y-1">
                        <p><span class="font-semibold">Plafon Saat Ini:</span> Rp {{ number_format($submission->plafon, 0, ',', '.') }}</p>
                        <p><span class="font-semibold">Kios:</span> {{ $submission->nama_kios }}</p>
                        <p class="text-xs text-blue-600 mt-2">Form di bawah sudah terisi otomatis dengan data customer ini. Anda dapat mengubah plafon dan detail lainnya sesuai kebutuhan.</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('submissions.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Hidden fields -->
            <input type="hidden" name="plafon_type" value="open">

            <!-- Kode Otomatis (Read-only) -->
            <div class="mb-6 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                <div class="flex items-start">
                    <div>
                        <p class="text-sm font-semibold text-indigo-900">Kode Pengajuan</p>
                        <p class="text-sm font-bold text-black mt-1">{{ $kode }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama (Pre-filled, editable) -->
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

                <!-- Nama Kios (Pre-filled, editable) -->
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

                <!-- Alamat (Pre-filled, editable) -->
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

                <!-- Plafon (Editable - untuk open plafon baru) -->
                <div>
                    <label for="plafon" class="block text-sm font-semibold text-gray-700 mb-2">
                        Plafon Baru (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="plafon" id="plafon" value="{{ old('plafon', $submission->plafon) }}" min="0" step="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('plafon') border-red-500 @enderror"
                        placeholder="Contoh: 5000000">
                    @error('plafon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan plafon yang diinginkan untuk open plafon baru</p>
                </div>

                <!-- Jumlah Buka Faktur -->
                <div>
                    <label for="jumlah_buka_faktur" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah Buka Faktur <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah_buka_faktur" id="jumlah_buka_faktur" value="{{ old('jumlah_buka_faktur', $submission->jumlah_buka_faktur) }}" min="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jumlah_buka_faktur') border-red-500 @enderror"
                        placeholder="Contoh: 5">
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
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('komitmen_pembayaran') border-red-500 @enderror"
                        placeholder="Contoh: Pembayaran akan dilakukan setiap tanggal 25 setiap bulan">{{ old('komitmen_pembayaran', $submission->komitmen_pembayaran) }}</textarea>
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
                    Simpan Pengajuan Open Plafon
                </button>
            </div>
        </form>
    </div>
</div>
@endsection