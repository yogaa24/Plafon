@extends('layouts.app')

@section('title', 'Dashboard Approval Level 3')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Approval Level 3</h1>
        <p class="text-sm text-gray-600">Review dan proses pengajuan oleh Tim Approver Level 3</p>
        <p class="text-xs text-gray-500 mt-1">Approver: {{ auth()->user()->approver_name }}</p>
    </div>

    <!-- Filter Status Tabs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="flex border-b border-gray-200">
            <a href="{{ route('approvals.level3', ['status' => 'on_progress'] + request()->except('status')) }}" 
               class="flex-1 px-6 py-3 text-center font-medium text-sm transition {{ $statusFilter === 'on_progress' ? 'bg-indigo-50 text-indigo-700 border-b-2 border-indigo-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    On Progress
                </span>
            </a>
            <a href="{{ route('approvals.level3', ['status' => 'done'] + request()->except('status')) }}" 
               class="flex-1 px-6 py-3 text-center font-medium text-sm transition {{ $statusFilter === 'done' ? 'bg-green-50 text-green-700 border-b-2 border-green-600' : 'text-gray-600 hover:bg-gray-50' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Done
                </span>
            </a>
        </div>

        <!-- Filter & Search -->
        <div class="p-4">
            <form method="GET" action="{{ route('approvals.level3') }}" class="space-y-4">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                
                <div class="flex flex-wrap gap-3">
                    <!-- Search -->
                    <div class="flex-1 min-w-[250px]">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari kode, nama, kios, atau sales..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date From -->
                    <div class="w-44">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Date To -->
                    <div class="w-44">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2 ml-auto">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                            Filter
                        </button>
                        <a href="{{ route('approvals.level3', ['status' => $statusFilter]) }}" 
                           class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama / Kios</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider" colspan="{{ $level3Approvers->count() }}">Status Approver</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th colspan="6"></th>
                        @foreach($level3Approvers as $approver)
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-600">
                            {{ $approver->approver_name }}
                        </th>
                        @endforeach
                        <th></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($submissions as $index => $submission)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $submissions->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $submission->kode }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $submission->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $submission->nama_kios }}</div>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {!! $submission->plafon_type_badge !!}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $submission->sales->name }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($submission->plafon_type === 'rubah' && $submission->previousSubmission)
                                <div class="flex flex-col space-y-1">
                                    <span class="text-xs text-gray-400 line-through">
                                        {{ number_format($submission->previousSubmission->plafon, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-semibold {{ $submission->plafon > $submission->previousSubmission->plafon ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($submission->plafon, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($submission->plafon, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        
                        <!-- Status untuk setiap approver -->
                        @foreach($level3Approvers as $approver)
                        @php
                            $approval = $submission->approvals->where('approver_id', $approver->id)->first();
                        @endphp
                        <td class="px-2 py-3 text-center">
                            @if($approval)
                                @if($approval->status === 'approved')
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-green-100 text-green-600 rounded-full font-bold text-lg" title="Disetujui oleh {{ $approver->approver_name }}">
                                        ✔
                                    </span>
                                @elseif($approval->status === 'rejected')
                                    <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 rounded-full font-bold text-lg" title="Ditolak oleh {{ $approver->approver_name }}">
                                        ✖
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-400 rounded-full font-bold text-lg" title="Belum ada keputusan dari {{ $approver->approver_name }}">
                                    –
                                </span>
                            @endif
                        </td>
                        @endforeach
                        
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @php
                                $userApproval = $submission->approvals->where('approver_id', auth()->id())->first();
                                $hasAnyApproval = $submission->approvals->where('status', 'approved')->count() > 0;
                            @endphp
                            
                            @if($userApproval)
                                <!-- Sudah memberikan keputusan -->
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Detail Button -->
                                    <button onclick="toggleDetail({{ $submission->id }})" 
                                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                                            title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    
                                    <span class="text-xs px-3 py-1 rounded-full font-semibold
                                        {{ $userApproval->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $userApproval->status === 'approved' ? 'Anda Setujui' : 'Anda Tolak' }}
                                    </span>
                                </div>
                            @elseif($hasAnyApproval)
                                <!-- Ada approver lain yang sudah approve, hanya tampilkan tombol detail -->
                                <button onclick="toggleDetail({{ $submission->id }})" 
                                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                                        title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            @else
                                <!-- Belum ada yang approve, tampilkan semua tombol -->
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Detail Button -->
                                    <button onclick="toggleDetail({{ $submission->id }})" 
                                            class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                                            title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Approve Button -->
                                    <form action="{{ route('approvals.process', $submission) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menyetujui pengajuan ini?')">
                                        @csrf
                                        <input type="hidden" name="action" value="approved">
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" title="ACC">
                                            Acc
                                        </button>
                                    </form>
                                    
                                    <!-- Reject Button -->
                                    <button onclick="openRejectModal({{ $submission->id }})" 
                                            class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition" 
                                            title="Tolak">
                                        Rejc
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Detail Row (Hidden by default) -->
                    <tr id="detail-{{ $submission->id }}" class="hidden bg-gray-50">
                        <td colspan="{{ 7 + $level3Approvers->count() }}" class="px-4 py-4">
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
                                        @if($submission->plafon_type === 'rubah' && $submission->previousSubmission)
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon Sebelumnya:</span>
                                            <span class="text-sm text-gray-500">Rp {{ number_format($submission->previousSubmission->plafon, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon {{ $submission->plafon_type === 'rubah' ? 'Baru' : '' }}:</span>
                                            <span class="text-sm font-bold text-indigo-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                                        </div>
                                        @if($submission->plafon_type === 'open')
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Jumlah Buka (Rp.):</span>
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

                                <!-- Riwayat Approval Level 3 -->
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Riwayat Keputusan Level 3</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($level3Approvers as $approver)
                                        @php
                                            $approval = $submission->approvals->where('approver_id', $approver->id)->first();
                                        @endphp
                                        <div class="p-3 rounded-lg border 
                                            {{ $approval && $approval->status === 'approved' ? 'bg-green-50 border-green-200' : 
                                               ($approval && $approval->status === 'rejected' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200') }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-sm text-gray-900">{{ $approver->approver_name }}</span>
                                                @if($approval)
                                                    <span class="text-xs px-2 py-1 rounded-full font-semibold
                                                        {{ $approval->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                        {{ $approval->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                                    </span>
                                                @else
                                                    <span class="text-xs px-2 py-1 rounded-full font-semibold bg-gray-200 text-gray-600">
                                                        No Respon
                                                    </span>
                                                @endif
                                            </div>
                                            @if($approval)
                                                <p class="text-xs text-gray-500 mb-1">{{ $approval->created_at->format('d M Y H:i') }}</p>
                                                @if($approval->note)
                                                    <p class="text-xs text-gray-700 bg-white bg-opacity-50 p-2 rounded">{{ $approval->note }}</p>
                                                @endif
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 7 + $level3Approvers->count() }}" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pengajuan</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $statusFilter === 'on_progress' ? 'Belum ada pengajuan yang menunggu keputusan' : 'Belum ada pengajuan yang selesai' }}
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($submissions->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $submissions->appends(request()->except('page'))->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Info -->
    <div class="text-sm text-gray-600">
        Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} pengajuan
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tolak Pengajuan</h3>
            
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="rejected">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan Penolakan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="note" rows="4" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                              placeholder="Jelaskan alasan penolakan..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Tolak Pengajuan
                    </button>
                </div>
            </form>
        </div>
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
function toggleDetail(id) {
    const detailRow = document.getElementById('detail-' + id);
    
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
    } else {
        detailRow.classList.add('hidden');
    }
}

function openRejectModal(submissionId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    
    form.action = `/approvals/${submissionId}/process`;
    modal.classList.remove('hidden');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
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