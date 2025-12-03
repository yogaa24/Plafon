@extends('layouts.app')

@section('title', 'Buat Pengajuan Baru')

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
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Open Plafon Sementara</h1>
            <p class="text-indigo-100 text-sm mt-1">Isi formulir di bawah untuk membuat pengajuan penjualan</p>
        </div>

        <form action="{{ route('submissions.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Kode Otomatis -->
            <div class="mb-6 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                <p class="text-sm font-bold text-black mt-1">{{ $kode }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Nama (INPUT BIASA) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Ketik nama baru atau nama pelanggan...">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Kios -->
                <div>
                    <label for="nama_kios" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Kios <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kios" id="nama_kios" value="{{ old('nama_kios') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('nama_kios') border-red-500 @enderror">
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
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plafon -->
                <div>
                    <label for="plafon" class="block text-sm font-semibold text-gray-700 mb-2">
                        Plafon (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="plafon" id="plafon" value="{{ old('plafon') }}" min="0" step="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Contoh: 5000000">
                    <p class="mt-1 text-xs text-gray-500">Masukkan nilai tanpa titik/koma</p>
                </div>

                <!-- Jumlah Buka Faktur -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah Buka Faktur <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah_buka_faktur" id="jumlah_buka_faktur" value="{{ old('jumlah_buka_faktur') }}" min="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Contoh: 5">
                </div>

                <!-- Komitmen Pembayaran -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <textarea name="komitmen_pembayaran" id="komitmen_pembayaran" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Contoh: Pembayaran dilakukan setiap tanggal 25">{{ old('komitmen_pembayaran') }}</textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('submissions.index') }}" 
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 shadow-md">
                    Simpan Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
