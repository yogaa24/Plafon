@extends('layouts.app')

@section('title', 'Detail Customer')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Customer</h1>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap customer dan riwayat pengajuan</p>
        </div>
        <a href="{{ route('piutang-manager.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white text-sm rounded-lg hover:bg-gray-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Customer Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900">{{ $customer->nama }}</h2>
                <p class="text-gray-600">{{ $customer->nama_kios }}</p>
                <span class="inline-block mt-1 px-3 py-1 text-xs font-semibold rounded-full 
                    {{ $customer->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Kode Customer</label>
                    <p class="text-base font-mono text-gray-900">{{ $customer->kode_customer }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Sales</label>
                    <p class="text-base text-gray-900">{{ $customer->sales->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Alamat</label>
                    <p class="text-base text-gray-900">{{ $customer->alamat }}</p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Plafon Aktif</label>
                    <p class="text-2xl font-bold text-green-700">
                        Rp {{ number_format($customer->plafon_aktif, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Piutang</label>
                    <p class="text-2xl font-bold {{ $customer->piutang > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        @if($customer->piutang > 0)
                            Rp {{ number_format($customer->piutang, 0, ',', '.') }}
                        @else
                            Rp 0
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Terdaftar Sejak</label>
                    <p class="text-base text-gray-900">{{ $customer->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission History
    @if($customer->submissions && $customer->submissions->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Riwayat Pengajuan</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Plafon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customer->submissions->sortByDesc('created_at') as $submission)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $submission->kode }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($submission->plafon_type === 'open')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Open</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Rubah</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                            Rp {{ number_format($submission->plafon, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {!! $submission->status_badge !!}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $submission->created_at->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum Ada Pengajuan</h3>
        <p class="mt-2 text-sm text-gray-500">Customer ini belum memiliki riwayat pengajuan plafon.</p>
    </div>
    @endif -->
</div>
@endsection