@extends('layouts.app')

@section('title', 'Dashboard Approval')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Approval Level {{ $level }}</h1>
            <p class="text-sm text-gray-600">Review dan proses pengajuan yang menunggu approval Anda</p>
        </div>
        <!-- TAMBAHKAN TOMBOL INI -->
        <a href="{{ route('approvals.history') }}" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Lihat Riwayat
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('approvals.index') }}" id="filterForm" class="space-y-4">
            <!-- Row 1: Search & Date Range -->
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Cari kode, nama, kios, atau sales..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Sales Filter dengan Auto Submit -->
                <div class="w-52">
                    <select name="sales_id" 
                        onchange="document.getElementById('filterForm').submit()" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-green-500 cursor-pointer">
                        <option value="">Semua Sales</option>
                        @foreach($salesList as $sales)
                        <option value="{{ $sales->id }}" {{ request('sales_id') == $sales->id ? 'selected' : '' }}>
                            {{ $sales->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div class="w-44">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Dari Tanggal">
                </div>

                <!-- Date To -->
                <div class="w-44">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                        placeholder="Sampai Tanggal">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 ml-auto">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Filter
                    </button>
                    <a href="{{ route('approvals.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-16">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Kios</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jumlah Value Faktur</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Riwayat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($submissions as $index => $submission)
                    <!-- Main Row -->
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center space-y-1">
                                <span class="text-sm font-semibold text-gray-900">{{ $submissions->firstItem() + $index }}</span>
                                <button type="button" onclick="toggleDetail({{ $submission->id }})" class="text-gray-500 hover:text-indigo-600 focus:outline-none transition" title="Lihat Detail">
                                    <svg id="icon-{{ $submission->id }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $submission->nama }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-900">{{ $submission->nama_kios }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $submission->sales->name }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($submission->plafon_type === 'open')
                                <span class="text-sm text-gray-900">{{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</span>
                            @else
                                <span class="text-sm text-gray-400 italic">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($submission->plafon_type === 'rubah' && $submission->customer)
                                <div class="flex flex-col items-center space-y-1">
                                    <!-- Plafon Lama -->
                                    <span class="text-xs text-gray-400 line-through">
                                        {{ number_format($submission->customer->plafon_aktif, 0, ',', '.') }}
                                    </span>
                                    <!-- Plafon Baru -->
                                    <span class="text-sm font-semibold {{ $submission->plafon > $submission->customer->plafon_aktif ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($submission->plafon, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ number_format($submission->plafon, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {!! $submission->plafon_type_badge !!}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $submission->created_at->format('d M Y') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <!-- Approval History Indicator -->
                            <div class="flex items-center justify-center space-x-1">
                                @for($i = 1; $i < $level; $i++)
                                    @php
                                        $approval = $submission->approvals->where('level', $i)->first();
                                    @endphp
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                        @if($approval && $approval->status == 'approved') bg-green-500 text-white
                                        @else bg-gray-200 text-gray-500
                                        @endif">
                                        {{ $i }}
                                    </div>
                                @endfor
                                <!-- Current Level -->
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-yellow-500 text-white">
                                    {{ $level }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-2">
                        <!-- Approve Button -->
                        @if($level == 2 && $submission->plafon_type === 'open')
                            <!-- Level 2 Open Plafon: Modal dengan payment data -->
                            <button onclick="openApprovalModal({{ $submission->id }}, 'approved')" 
                                    class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                    title="Setujui">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @elseif($level == 2 && $submission->plafon_type === 'rubah')
                            <!-- Level 2 Rubah Plafon: Modal dengan lampiran -->
                            <button onclick="openApprovalModal({{ $submission->id }}, 'approved')" 
                                    class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                    title="Setujui">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @else
                            <!-- Level 1 dan level lainnya: Modal simple (hanya notes) -->
                            <button onclick="openApprovalModalSimple({{ $submission->id }}, 'approved')" 
                                    class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                    title="Setujui">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        @endif
                        
                        <!-- Reject Button - Semua level pakai openApprovalModal untuk reject -->
                        <button onclick="openApprovalModal({{ $submission->id }}, 'rejected')" 
                                class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition" 
                                title="Tolak">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                        </td>
                    </tr>
                    
                    <!-- Detail Row (Hidden by default) -->
                    <tr id="detail-{{ $submission->id }}" class="hidden bg-gray-50">
                        <td colspan="11" class="px-4 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white rounded-lg border border-gray-200">
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Umum</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Kode:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $submission->kode }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Jenis:</span>
                                            <span class="text-sm font-medium">
                                                @if($submission->plafon_type === 'open')
                                                    <span class="text-blue-600">Open Plafon</span>
                                                @else
                                                    <span class="text-purple-600">Rubah Plafon 
                                                        @if($submission->plafon_direction)
                                                            ({{ $submission->plafon_direction === 'naik' ? '↑ Naik' : '↓ Turun' }})
                                                        @endif
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Nama:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $submission->nama }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Nama Kios:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $submission->nama_kios }}</span>
                                        </div>
                                        <div class="py-1">
                                            <span class="text-sm text-gray-600 block mb-1">Alamat:</span>
                                            <span class="text-sm text-gray-900">{{ $submission->alamat }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Keuangan</h4>
                                    <div class="space-y-2">
                                        @if($submission->plafon_type === 'rubah' && $submission->customer)
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon Sebelumnya:</span>
                                            <span class="text-sm text-gray-500">Rp {{ number_format($submission->customer->plafon_aktif, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon {{ $submission->plafon_type === 'rubah' ? 'Baru' : '' }}:</span>
                                            <span class="text-sm font-bold text-indigo-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                                        </div>
                                        @if($submission->plafon_type === 'open')
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Jumlah Value Faktur</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Sales:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $submission->sales->name }}</span>
                                        </div>
                                        <div class="py-1">
                                            <span class="text-sm text-gray-600 block mb-1">Komitmen Pembayaran:</span>
                                            <span class="text-sm text-gray-900">{{ $submission->komitmen_pembayaran }}</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span class="text-sm text-gray-600">Dibuat:</span>
                                            <span class="text-sm text-gray-500">{{ $submission->created_at->format('d M Y, H:i') }}</span>
                                        </div>

                                        <!-- Tambahkan di bagian detail information, setelah informasi keuangan -->
                                        @if($submission->lampiran_path)
                                            @php
                                                $lampiranPaths = is_array($submission->lampiran_path)
                                                    ? $submission->lampiran_path
                                                    : json_decode($submission->lampiran_path, true);
                                            @endphp

                                            @if($lampiranPaths && count($lampiranPaths) > 0)
                                                <div class="col-span-1 md:col-span-2 mt-4">
                                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                                        Lampiran ({{ count($lampiranPaths) }} gambar)
                                                    </h4>

                                                    <div class="grid grid-cols-3 gap-2">
                                                        @foreach($lampiranPaths as $path)
                                                            <img
                                                                src="{{ asset($path) }}"
                                                                alt="Lampiran"
                                                                onclick="openImageModal('{{ asset($path) }}')"
                                                                class="w-full h-32 object-cover rounded-lg border-2 border-gray-300
                                                                    hover:border-indigo-500 transition cursor-pointer"
                                                            >
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- OD/Over Information Section -->
                                        @if($submission->payment_type)
                                        <div class="col-span-1 md:col-span-2">
                                            <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Pembayaran</h4>

                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                <div class="flex items-start">

                                                    <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>

                                                    <div class="flex-1">
                                                        <p class="text-sm font-semibold text-blue-900 mb-2">
                                                            Jenis: <span class="uppercase">{{ $submission->payment_type }}</span>
                                                        </p>

                                                        @php
                                                            $paymentData = is_array($submission->payment_data)
                                                                ? $submission->payment_data
                                                                : json_decode($submission->payment_data, true);
                                                        @endphp

                                                        @if($paymentData && count($paymentData) > 0)

                                                        {{-- GRID 2 KOLOM / TIDAK PANJANG KE BAWAH --}}
                                                        <div class="grid grid-cols-2 gap-y-2 gap-x-6">

                                                            @foreach($paymentData as $key => $value)
                                                                @if($value)
                                                                <div class="flex justify-between text-sm">
                                                                    <span class="text-blue-700">
                                                                        @if($key === 'piutang') Piutang
                                                                        @elseif($key === 'jml_over') Jml Over
                                                                        @elseif($key === 'od_30') Jml OD 30
                                                                        @elseif($key === 'od_60') Jml OD 60
                                                                        @elseif($key === 'od_90') Jml OD 90
                                                                        @endif:
                                                                    </span>
                                                                    <span class="font-semibold text-blue-900">
                                                                        Rp {{ number_format($value, 0, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                                @endif
                                                            @endforeach

                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        <!-- Data OD/Over dari Approval Level 2 -->
                                        @php
                                            $approval2 = $submission->approvals->where('level', 2)->where('status', 'approved')->first();
                                        @endphp
                                        @if($approval2)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase">Data Verifikasi Level 2</h5>
                                            <div class="space-y-1">
                                                <div class="flex justify-between py-1">
                                                    <span class="text-xs text-gray-600">Piutang:</span>
                                                    <span class="text-xs font-medium text-gray-900">Rp {{ number_format($approval2->piutang ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-xs text-gray-600">Jml Over:</span>
                                                    <span class="text-xs font-medium text-gray-900">Rp {{ number_format($approval2->jml_over ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-xs text-gray-600">Jml OD 30:</span>
                                                    <span class="text-xs font-medium text-gray-900">Rp {{ number_format($approval2->jml_od_30 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-xs text-gray-600">Jml OD 60:</span>
                                                    <span class="text-xs font-medium text-gray-900">Rp {{ number_format($approval2->jml_od_60 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-xs text-gray-600">Jml OD 90:</span>
                                                    <span class="text-xs font-medium text-gray-900">Rp {{ number_format($approval2->jml_od_90 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Riwayat Approval -->
                                @if($submission->approvals->count() > 0)
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Riwayat Approval</h4>
                                    <div class="space-y-2">
                                        @foreach($submission->approvals as $approval)
                                        <div class="flex items-start justify-between p-3 rounded-lg border 
                                            {{ $approval->status === 'approved' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm 
                                                    {{ $approval->status === 'approved' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                    {{ $approval->level }}
                                                </div>
                                                <div>
                                                    <!-- TAMPILKAN NAMA LENGKAP TERMASUK TC - NAMA -->
                                                    <p class="font-semibold text-gray-900 text-sm">{{ $approval->approver->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $approval->created_at->format('d M Y H:i') }}</p>
                                                    @if($approval->note)
                                                        <p class="text-xs text-gray-700 mt-1 italic">"{{ $approval->note }}"</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full font-semibold
                                                {{ $approval->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $approval->status === 'approved' ? '✓ Disetujui' : '✖ Ditolak' }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pengajuan</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ request('search') ? 'Tidak ada hasil yang cocok dengan filter' : 'Belum ada pengajuan yang menunggu approval Anda' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($submissions->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Info -->
    <div class="text-sm text-gray-600">
        Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} pengajuan
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-[2px] overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4"></h3>
            
            <!-- TAMBAHKAN method="POST" di sini -->
            <form id="approvalForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action" id="actionInput" value="">
                <input type="hidden" name="jenis_pembayaran" id="jenisPembayaranInput" value="">
                
                <!-- Level 2 Specific Fields -->
                <div id="level2Fields" class="hidden space-y-4 mb-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-gray-900">Informasi Verifikasi (Level 2)</h4>
                            <span id="dataSourceInfo" class="text-xs px-2 py-1 rounded bg-blue-50 text-blue-700"></span>
                        </div>
                        
                        <!-- Jenis Pembayaran Display Only -->
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pembayaran</label>
                            <div id="jenisPembayaranDisplay" class="px-4 py-2 rounded-lg text-sm font-semibold"></div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Piutang <span class="text-red-500">*</span></label>
                                <input type="number" name="piutang" id="piutangInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jml Over <span class="text-red-500">*</span></label>
                                <input type="number" name="jml_over" id="jmlOverInput"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jml OD 30 <span class="text-red-500">*</span></label>
                                <input type="number" name="jml_od_30" id="jmlOd30Input" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jml OD 60 <span class="text-red-500">*</span></label>
                                <input type="number" name="jml_od_60" id="jmlOd60Input" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jml OD 90 <span class="text-red-500">*</span></label>
                                <input type="number" name="jml_od_90" id="jmlOd90Input" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
                            <strong>💡 Info:</strong> Data di atas adalah data yang diisi oleh sales. Anda dapat memverifikasi dan mengubahnya jika diperlukan.
                        </div>
                    </div>
                </div>

                <!-- TAMBAHKAN bagian upload lampiran DI SINI (di luar level2Fields) -->
                <div id="lampiranSection" class="hidden mb-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Lampiran <span class="text-gray-400">(Maksimal 3 gambar)</span>
                        </label>
                        <input type="file" 
                            name="lampiran[]" 
                            id="lampiranInput" 
                            accept="image/jpeg,image/jpg,image/png" 
                            multiple
                            max="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="previewMultipleLampiran(event)">
                        <p class="text-xs text-gray-500 mt-1">
                            Format: JPG, JPEG, PNG. Gambar akan otomatis dikompres menjadi ±500KB
                        </p>
                        
                        <!-- Preview Multiple Images -->
                        <div id="lampiranPreviewContainer" class="mt-3 hidden">
                            <div id="lampiranPreviewList" class="grid grid-cols-3 gap-2"></div>
                            <p class="text-xs text-gray-500 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span id="imageCountText">0 gambar dipilih</span> - Gambar akan dikompres otomatis saat diupload
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Catatan textarea tetap di bawah -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan <span id="noteRequired" class="text-red-500">*</span>
                    </label>
                    <textarea id="approvalNote" name="note" rows="4" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                            placeholder="Catatan wajib diisi untuk setiap tindakan..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeApprovalModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" id="submitButton" class="flex-1 px-4 py-2 text-white rounded-lg transition">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div id="imageModal"
     class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">

    <div class="relative max-w-4xl w-full">
        <!-- Close Button -->
        <button onclick="closeImageModal()"
                class="absolute -top-3 -right-3 bg-red-600 ring-2 ring-black hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Image -->
        <img id="imageModalContent"
             src=""
             alt="Preview Lampiran"
             class="w-full max-h-[85vh] object-contain rounded-lg shadow-lg bg-white">
    </div>
</div>

@if(session('success'))
<div id="success-alert" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div id="error-alert" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('error') }}
</div>
@endif

<script>
// Store current level from Laravel
const currentLevel = {{ $level }};
const submissionsData = @json($submissionsArray);

// Store plafon dan value faktur per submission
const submissionDetails = {};
submissionsData.forEach(s => {
    submissionDetails[s.id] = {
        plafonAktif: s.plafon || 0,
        valueFaktur: s.jumlah_buka_faktur || 0
    };
});

function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('imageModalContent');

    img.src = src;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('imageModalContent');

    modal.classList.add('hidden');
    img.src = '';
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    const salesSelect = document.querySelector('select[name="sales_id"]');
    const form = document.getElementById('filterForm');
    
    salesSelect.addEventListener('change', function() {
        // Optional: Tampilkan loading indicator
        const selectElement = this;
        selectElement.style.opacity = '0.6';
        selectElement.style.pointerEvents = 'none';
        
        // Submit form
        form.submit();
    });
});

function toggleDetail(id) {
    const detailRow = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        detailRow.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Modal untuk Level 2 Open Plafon (dengan payment data)
function openApprovalModal(submissionId, action) {
    const modal = document.getElementById('approvalModal');
    const modalTitle = document.getElementById('modalTitle');
    const approvalNote = document.getElementById('approvalNote');
    const noteRequired = document.getElementById('noteRequired');
    const submitButton = document.getElementById('submitButton');
    const form = document.getElementById('approvalForm');
    const actionInput = document.getElementById('actionInput');
    const level2Fields = document.getElementById('level2Fields');
    const lampiranSection = document.getElementById('lampiranSection');
    
    // Get submission data
    const submission = submissionsData.find(s => s.id === submissionId);
    const jenisPembayaran = submission ? submission.payment_type : '';
    const paymentData = submission ? submission.payment_data : {};
    const plafonType = submission ? submission.plafon_type : '';
    const jmlOverInput = document.getElementById('jmlOverInput');

    // Set form action
    form.action = `/approvals/${submissionId}/process`;
    actionInput.value = action;
    
    // Set jenis pembayaran hidden input
    document.getElementById('jenisPembayaranInput').value = jenisPembayaran;
    
    // RESET semua field dan section
    level2Fields.classList.add('hidden');
    lampiranSection.classList.add('hidden');
    
    // Remove required dari payment fields
    ['piutangInput', 'jmlOverInput', 'jmlOd30Input', 'jmlOd60Input', 'jmlOd90Input'].forEach(id => {
        const elem = document.getElementById(id);
        if (elem) elem.required = false;
    });
    
    // LOGIKA BERDASARKAN ACTION DAN PLAFON TYPE
    if (action === 'approved' && currentLevel === 2 && plafonType === 'open') {
    // ===== LEVEL 2 OPEN PLAFON =====
    level2Fields.classList.remove('hidden');
    lampiranSection.classList.remove('hidden');
    displayExistingLampiran(submissionId); //lampiran sc
    
    /* PRE-FILL DATA DULU sebelum logic OVER/OD */
    document.getElementById('piutangInput').value = paymentData.piutang || '';
    document.getElementById('jmlOverInput').value = paymentData.jml_over || ''; // ✅ ISI DULU
    document.getElementById('jmlOd30Input').value = paymentData.jml_od_30 || paymentData.od_30 || '';
    document.getElementById('jmlOd60Input').value = paymentData.jml_od_60 || paymentData.od_60 || '';
    document.getElementById('jmlOd90Input').value = paymentData.jml_od_90 || paymentData.od_90 || '';
    
    /* RESET STATE SETIAP MODAL DIBUKA */
    jmlOverInput.readOnly = false;
    jmlOverInput.disabled = false;
    jmlOverInput.classList.remove('bg-gray-100');

    /* RULE BERDASARKAN JENIS PEMBAYARAN */
    if (jenisPembayaran === 'over') {
        // OVER → auto hitung & kunci
        jmlOverInput.readOnly = true;
        jmlOverInput.classList.add('bg-gray-100');
        setupOverAutoCalculation(submissionId);
    } else {
        // OD → manual, value sudah terisi di atas
        jmlOverInput.readOnly = false;
        jmlOverInput.disabled = false;
        jmlOverInput.classList.remove('bg-gray-100');
    }
    
    // Display jenis pembayaran
    const jenisPembayaranDisplay = document.getElementById('jenisPembayaranDisplay');
    if (jenisPembayaran === 'over') {
        jenisPembayaranDisplay.innerHTML = '<span class="px-3 py-1 bg-purple-100 text-purple-700 rounded">OVER</span>';
    } else {
        jenisPembayaranDisplay.innerHTML = '<span class="px-3 py-1 bg-orange-100 text-orange-700 rounded">OD</span>';
    }

    // Info sumber data
    const dataSourceInfo = document.getElementById('dataSourceInfo');
    if (paymentData && (paymentData.piutang || paymentData.jml_over || paymentData.od_30 || paymentData.jml_od_30)) {
        dataSourceInfo.textContent = '✓ Data dari Sales';
        dataSourceInfo.className = 'text-xs px-2 py-1 rounded bg-green-50 text-green-700';
    } else {
        dataSourceInfo.textContent = 'Data Belum Diisi Sales';
        dataSourceInfo.className = 'text-xs px-2 py-1 rounded bg-orange-50 text-orange-700';
    }

    // Set Required untuk payment fields
    ['piutangInput', 'jmlOverInput', 'jmlOd30Input', 'jmlOd60Input', 'jmlOd90Input'].forEach(id => {
        document.getElementById(id).required = true;
    });
    
    modalTitle.textContent = 'Setujui Pengajuan - Verifikasi Data (Open Plafon)';
    approvalNote.placeholder = 'Catatan tambahan (wajib diisi)...';
    approvalNote.required = true;
    noteRequired.classList.remove('hidden');
    submitButton.className = 'flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
    submitButton.textContent = 'Setujui';

    } else if (action === 'approved' && currentLevel === 2 && plafonType === 'rubah') {
        // ===== LEVEL 2 RUBAH PLAFON =====
        level2Fields.classList.add('hidden');
        lampiranSection.classList.remove('hidden');
        
        modalTitle.textContent = 'Setujui Pengajuan - Rubah Plafon';
        approvalNote.placeholder = 'Catatan tambahan (wajib diisi)...';
        approvalNote.required = true;
        noteRequired.classList.remove('hidden');
        submitButton.className = 'flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
        submitButton.textContent = 'Setujui';

    } else if (action === 'rejected') {
        // ===== REJECT (SEMUA LEVEL) =====
        level2Fields.classList.add('hidden');
        lampiranSection.classList.add('hidden');
        
        modalTitle.textContent = 'Tolak Pengajuan';
        approvalNote.placeholder = 'Jelaskan alasan penolakan...';
        approvalNote.required = true;
        noteRequired.classList.remove('hidden');
        submitButton.className = 'flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition';
        submitButton.textContent = 'Tolak';
        
    } else {
        // ===== FALLBACK (LEVEL LAIN) =====
        level2Fields.classList.add('hidden');
        lampiranSection.classList.add('hidden');
        
        modalTitle.textContent = 'Konfirmasi Approval';
        approvalNote.placeholder = 'Catatan (wajib diisi)...';
        approvalNote.required = true;
        noteRequired.classList.remove('hidden');
        submitButton.className = 'flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition';
        submitButton.textContent = 'Konfirmasi';
    }
    
    // Clear note
    approvalNote.value = '';
    
    // Show modal
    modal.classList.remove('hidden');
}


function previewMultipleLampiran(event) {
    const files = Array.from(event.target.files);
    const previewContainer = document.getElementById('lampiranPreviewContainer');
    const previewList = document.getElementById('lampiranPreviewList');
    const imageCountText = document.getElementById('imageCountText');
    const inputElement = event.target;
    
    // Validasi jumlah file
    if (files.length > 3) {
        alert('Maksimal 3 gambar yang dapat diupload');
        inputElement.value = '';
        previewContainer.classList.add('hidden');
        return;
    }
    
    if (files.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    // Clear previous previews
    previewList.innerHTML = '';
    
    let validFiles = [];
    
    files.forEach((file, index) => {
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert(`File "${file.name}" bukan format gambar yang valid (JPG/PNG)`);
            return;
        }
        
        // Validasi ukuran file (max 10MB sebelum compress)
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" terlalu besar (max 10MB)`);
            return;
        }
        
        validFiles.push(file);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'relative';
            previewItem.innerHTML = `
                <img src="${e.target.result}" 
                     class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 shadow-sm" 
                     alt="Preview ${index + 1}">
                <button type="button" 
                        onclick="removePreviewImage(${index})" 
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition shadow-lg"
                        title="Hapus gambar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="text-center mt-1">
                    <span class="text-xs text-gray-500">${(file.size / 1024).toFixed(0)} KB</span>
                </div>
            `;
            previewList.appendChild(previewItem);
        }
        reader.readAsDataURL(file);
    });
    
    // Update counter
    imageCountText.textContent = `${validFiles.length} gambar dipilih`;
    
    if (validFiles.length > 0) {
        previewContainer.classList.remove('hidden');
    } else {
        inputElement.value = '';
        previewContainer.classList.add('hidden');
    }
}

// Fungsi untuk menghapus preview individual
function removePreviewImage(index) {
    const inputElement = document.getElementById('lampiranInput');
    const dt = new DataTransfer();
    const files = Array.from(inputElement.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    inputElement.files = dt.files;
    
    // Trigger preview update
    previewMultipleLampiran({ target: inputElement });
}

// Function untuk menghapus preview dan reset input file
function removeLampiranPreview() {
    const input = document.getElementById('lampiranInput');
    const previewContainer = document.getElementById('lampiranPreviewContainer');
    const previewList = document.getElementById('lampiranPreviewList');
    
    input.value = '';
    previewList.innerHTML = '';
    previewContainer.classList.add('hidden');
}

function displayExistingLampiran(submissionId) {
    const submission = submissionsData.find(s => s.id === submissionId);
    
    // Cek apakah ada lampiran_path di submission
    // Anda perlu menambahkan lampiran_path ke $submissionsArray di controller
    if (submission && submission.lampiran_path) {
        const lampiranSection = document.getElementById('lampiranSection');
        const existingLampiranDiv = document.createElement('div');
        existingLampiranDiv.className = 'mb-3 p-3 bg-blue-50 rounded border border-blue-200';
        existingLampiranDiv.innerHTML = `
            <p class="text-xs font-semibold text-blue-800 mb-2">
                📎 Lampiran dari Sales (${submission.lampiran_count || 0} gambar):
            </p>
            <div class="text-xs text-blue-600">
                <a href="/submissions/${submissionId}" target="_blank" class="underline hover:text-blue-800">
                    Lihat lampiran yang sudah diupload
                </a>
            </div>
        `;
        
        // Insert sebelum input file
        const lampiranInput = document.getElementById('lampiranInput');
        lampiranInput.parentElement.insertBefore(existingLampiranDiv, lampiranInput);
    }
}

// Update fungsi closeApprovalModal untuk reset preview juga
function closeApprovalModal() {
    const modal = document.getElementById('approvalModal');
    modal.classList.add('hidden');
    
    // Reset form
    const form = document.getElementById('approvalForm');
    form.reset();
    
    // Reset preview lampiran
    removeLampiranPreview();
}

// Auto-calculation untuk Jml Over di modal approval
function setupOverAutoCalculation(submissionId) {
    const piutangInput = document.getElementById('piutangInput');
    const jmlOverInput = document.getElementById('jmlOverInput');

    if (!piutangInput || !jmlOverInput) return;

    // ❗ Reset event listener lama
    piutangInput.oninput = null;
    piutangInput.onchange = null;

    const details = submissionDetails[submissionId];
    if (!details) return;

    const plafonAktif = details.plafonAktif;
    const valueFaktur = details.valueFaktur;

    function calculateJmlOver() {
        const piutang = parseFloat(piutangInput.value) || 0;
        const jmlOver = plafonAktif - (valueFaktur + piutang);

        jmlOverInput.value = Math.round(jmlOver);
    }

    piutangInput.addEventListener('input', calculateJmlOver);
    piutangInput.addEventListener('change', calculateJmlOver);

    if (piutangInput.value) {
        calculateJmlOver();
    }
}

// Modal SIMPLE untuk Level 1, Level 2 Rubah Plafon (hanya notes)
function openApprovalModalSimple(submissionId, action) {
    const modal = document.getElementById('approvalModal');
    const modalTitle = document.getElementById('modalTitle');
    const approvalNote = document.getElementById('approvalNote');
    const noteRequired = document.getElementById('noteRequired');
    const submitButton = document.getElementById('submitButton');
    const form = document.getElementById('approvalForm');
    const actionInput = document.getElementById('actionInput');
    const level2Fields = document.getElementById('level2Fields');
    const lampiranSection = document.getElementById('lampiranSection');
    
    // Hide level 2 fields
    level2Fields.classList.add('hidden');
    lampiranSection.classList.add('hidden'); 
    
    // Set form action
    form.action = `/approvals/${submissionId}/process`;
    actionInput.value = action;
    
    // Remove lampiran required
    const lampiranInput = document.getElementById('lampiranInput');
    if (lampiranInput) {
        lampiranInput.required = false;
    }
    
    // Configure for approve
    modalTitle.textContent = 'Setujui Pengajuan';
    approvalNote.placeholder = 'Jelaskan alasan persetujuan Anda...';
    approvalNote.required = true;
    noteRequired.classList.remove('hidden');
    submitButton.className = 'flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
    submitButton.textContent = 'Setujui';
    
    // Clear note
    approvalNote.value = '';
    
    modal.classList.remove('hidden');
}

function closeApprovalModal() {
    const modal = document.getElementById('approvalModal');
    modal.classList.add('hidden');
    
    // Reset form
    const form = document.getElementById('approvalForm');
    form.reset();
    
    // Reset preview lampiran
    removeLampiranPreview();

     // TAMBAHKAN INI
    const lampiranSection = document.getElementById('lampiranSection');
    if (lampiranSection) {
        lampiranSection.classList.add('hidden');
    }
    
    // TAMBAHKAN INI - Remove warning
    const warningDiv = document.getElementById('overWarningModal');
    if (warningDiv) {
        warningDiv.remove();
    }
}

// Close modal when clicking outside
document.getElementById('approvalModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});

// Auto hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = ['success-alert', 'error-alert'];
    alerts.forEach(alertId => {
        const alert = document.getElementById(alertId);
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }
    });
});
</script>
@endsection