@extends('layouts.app')

@section('title', 'Import Data Piutang')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Data Piutang</h1>
            <p class="text-sm text-gray-600 mt-1">
                Import data piutang customer dalam format teks
            </p>
        </div>
        <a href="{{ route('piutang-manager.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Instruksi
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold text-blue-900 mb-2">Format Data</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Format: <code class="bg-blue-100 px-2 py-0.5 rounded">KODE_CUSTOMER PIUTANG</code></li>
                    <li>• Satu baris untuk satu customer</li>
                    <li>• Pisahkan kode customer dan piutang dengan spasi atau tab</li>
                    <li>• Piutang harus berupa angka (boleh desimal)</li>
                    <li>• Contoh: <code class="bg-blue-100 px-2 py-0.5 rounded">C001 5000000</code></li>
                </ul>
            </div>
        </div>
    </div> -->

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-green-900">{{ session('success') }}</p>
                
                @if(session('success_count') > 0)
                    <p class="text-sm text-green-700 mt-1">
                        ✓ {{ session('success_count') }} customer berhasil diupdate
                    </p>
                @endif

                @if(session('error_count') > 0)
                    <p class="text-sm text-red-700 mt-2">
                        ✗ {{ session('error_count') }} baris gagal diproses
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(session('errors') && count(session('errors')) > 0)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h4 class="font-semibold text-red-900 mb-2">Detail Error:</h4>
        <ul class="text-sm text-red-800 space-y-1 max-h-60 overflow-y-auto">
            @foreach(session('errors') as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- @if(session('not_found') && count(session('not_found')) > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-900 mb-2">Customer Tidak Ditemukan:</h4>
        <div class="text-sm text-yellow-800 max-h-60 overflow-y-auto">
            <p class="mb-1">Kode customer berikut tidak ditemukan dalam database:</p>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach(session('not_found') as $kode)
                    <span class="bg-yellow-100 px-2 py-1 rounded text-xs">{{ $kode }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif -->

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-red-900 font-semibold">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Form Import -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('piutang-manager.import-piutang.process') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Data Piutang <span class="text-red-500">*</span>
                </label>
                <textarea name="piutang_data" rows="15" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="C001 5000000&#10;C002 7500000&#10;C003 3200000&#10;...">{{ old('piutang_data') }}</textarea>
                <p class="text-xs text-gray-500 mt-2">
                    Paste data dengan format: KODE_CUSTOMER [spasi] PIUTANG
                </p>
                @if($errors->has('piutang_data'))
                    <p class="text-red-600 text-sm mt-1">{{ $errors->first('piutang_data') }}</p>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Import Data
                    </span>
                </button>
                <button type="button" onclick="document.querySelector('textarea').value = ''" 
                        class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Clear
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyExample() {
    const exampleText = `C001 5000000
C002 7500000.50
C003 3200000
C004 10000000
C005 2500000.75`;
    
    navigator.clipboard.writeText(exampleText).then(() => {
        alert('Contoh data berhasil disalin!');
    });
}
</script>
@endsection