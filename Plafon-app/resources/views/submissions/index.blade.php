@extends('layouts.app')

@section('title', 'Daftar Pengajuan')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Customer</h1>
            <p class="text-sm text-gray-600">Kelola data customer dan pengajuan</p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('submissions.index') }}" id="filterForm" class="space-y-3">
            <div class="flex flex-wrap gap-3">
                <!-- View Type -->
                <div class="w-52">
                    <select name="view" id="viewFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="handleViewChange()">
                        <option value="">Customer</option>
                        <option value="submissions" {{ request('view') == 'submissions' ? 'selected' : '' }}>Pengajuan</option>
                    </select>
                </div>

                <!-- Status Filter (hanya muncul jika view = submissions) -->
                <div id="statusFilterWrapper" class="w-52 {{ request('view') != 'submissions' ? 'hidden' : '' }}">
                    <select name="status" id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Approval 1</option>
                        <option value="approved_1" {{ request('status') == 'approved_1' ? 'selected' : '' }}>Menunggu Approval 2</option>
                        <option value="approved_2" {{ request('status') == 'approved_2' ? 'selected' : '' }}>Menunggu Approval 3</option>
                        <option value="approved_3" {{ request('status') == 'approved_3' ? 'selected' : '' }}>Proses Input</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>✓ Selesai</option>
                        <option value="revision" {{ request('status') == 'revision' ? 'selected' : '' }}>Perlu Revisi</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Search untuk Pengajuan -->
                <div id="searchSubmissionWrapper" class="flex-1 min-w-[250px] {{ request('view') != 'submissions' ? 'hidden' : '' }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kios pengajuan..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Search untuk Customer -->
                <div id="searchCustomerWrapper" class="flex-1 min-w-[250px] {{ request('view') == 'submissions' ? 'hidden' : '' }}">
                    <input type="text" name="customer_search" value="{{ request('customer_search') }}" placeholder="Cari nama atau kios customer..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari
                    </button>
                    <a href="{{ route('submissions.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tampilan Customer (default view) -->
    @if(!$hasFilter && $customers->count() > 0)
    <div class="bg-white from-white rounded-lg shadow-sm border-transparent p-6">
        <div class="flex items-center mb-4">
            <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg font-bold text-black">Daftar Customer</h3>
            <span class="ml-auto text-sm text-green-700">Total: {{ $customers->count() }} customer</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-black">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-black uppercase tracking-wider w-16">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Nama Kios</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Alamat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-black uppercase tracking-wider">Plafon Aktif</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-black uppercase tracking-wider">Status Pengajuan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-black uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customers as $index => $customer)
                    <tr class="hover:bg-blue-50 transition">
                        <td class="px-4 py-3 text-center text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->kode_customer }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->nama_kios }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->alamat }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-700">
                            Rp {{ number_format($customer->plafon_aktif, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($customer->hasPendingSubmission())
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Sedang Diproses
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Tersedia
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                @if(!$customer->hasPendingSubmission())
                                    <a href="{{ route('submissions.create-open-plafon', $customer) }}" 
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition"
                                    title="Open Plafon Baru">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Open
                                    </a>
                                    <a href="{{ route('submissions.create-rubah-plafon', $customer) }}" 
                                    class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-lg hover:bg-purple-700 transition"
                                    title="Rubah Plafon">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Rubah
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500 italic">Menunggu approval</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif(!$hasFilter)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum Ada Customer</h3>
        <p class="mt-2 text-sm text-gray-500">Belum ada data customer yang tersedia.</p>
    </div>
    @endif

    <!-- Table Pengajuan (muncul jika ada filter) -->
    @if($hasFilter)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900">
                @if(request('status'))
                    Pengajuan - Status: 
                    @switch(request('status'))
                        @case('pending') Menunggu Approval 1 @break
                        @case('approved_1') Menunggu Approval 2 @break
                        @case('approved_2') Menunggu Approval 3 @break
                        @case('approved_3') Proses Input @break
                        @case('done') Selesai @break
                        @case('revision') Perlu Revisi @break
                        @case('rejected') Ditolak @break
                        @default Semua Status @break
                    @endswitch
                @elseif(request('search'))
                    Hasil Pencarian: "{{ request('search') }}"
                @else
                    Semua Pengajuan
                @endif
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider w-16">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Kios</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Alamat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Value Faktur</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
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
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-900">{{ $submission->alamat }}</span>
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
                                    <span class="text-xs text-gray-400 line-through">
                                        {{ number_format($submission->customer->plafon_aktif, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-semibold {{ $submission->plafon > $submission->customer->plafon_aktif ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($submission->plafon, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-sm font-semibold text-gray-900">
                                    Rp {{ number_format($submission->plafon, 0, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {!! $submission->plafon_type_badge !!}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {!! $submission->status_badge !!}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('submissions.show', $submission) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm" title="Detail Lengkap">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                
                                @if($submission->status === 'done')
                                    <form action="{{ route('submissions.destroy', $submission) }}" 
                                        method="POST" class="inline" 
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 font-medium text-sm" 
                                                title="Hapus Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a 1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                
                                @if(in_array($submission->status, ['pending', 'revision']))
                                <a href="{{ route('submissions.edit', $submission) }}" class="text-blue-600 hover:text-blue-900 font-medium text-sm" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('submissions.destroy', $submission) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Detail Row (Hidden by default) -->
                    <tr id="detail-{{ $submission->id }}" class="hidden bg-gray-50">
                        <td colspan="9" class="px-4 py-4">
                            <div class="p-4 bg-white rounded-lg border border-gray-200">
                                
                                <!-- Progress Section -->
                                <div class="mb-4 pb-4 border-b border-gray-200">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Progress Approval</h4>
                                    <div class="flex items-center justify-center space-x-4">
                                        @for($i = 1; $i <= 3; $i++)
                                            @php
                                                $approved = $submission->approvals->where('level', $i)->where('status', 'approved')->first();
                                                $rejected = $submission->approvals->where('level', $i)->where('status', 'rejected')->first();
                                                $revision = $submission->approvals->where('level', $i)->where('status', 'revision')->first();
                                            @endphp
                                            <div class="flex flex-col items-center">
                                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold
                                                    @if($approved) bg-green-500 text-white
                                                    @elseif($rejected) bg-red-500 text-white
                                                    @elseif($revision) bg-orange-500 text-white
                                                    @elseif($submission->current_level == $i && in_array($submission->status, ['pending', 'approved_1', 'approved_2'])) bg-yellow-500 text-white
                                                    @else bg-gray-200 text-gray-500
                                                    @endif">
                                                    {{ $i }}
                                                </div>
                                                <span class="text-xs text-gray-600 mt-2">Level {{ $i }}</span>
                                            </div>
                                            @if($i < 3)
                                            <div class="w-16 h-1 -mt-4 
                                                @if($approved) bg-green-500
                                                @else bg-gray-200
                                                @endif">
                                            </div>
                                            @endif
                                        @endfor
                                    </div>
                                </div>

                                <!-- Information Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                                        <span class="text-purple-600">Rubah Plafon</span>
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
                                            <div class="py-1">
                                                <span class="text-sm text-gray-600 block mb-1">Komitmen Pembayaran:</span>
                                                <span class="text-sm text-gray-900">{{ $submission->komitmen_pembayaran }}</span>
                                            </div>
                                            <div class="flex justify-between py-1">
                                                <span class="text-sm text-gray-600">Dibuat:</span>
                                                <span class="text-sm text-gray-500">{{ $submission->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- OD/Over Information Section -->
                                    @if($submission->payment_type)
                                    <div class="col-span-1 md:col-span-2">
                                        <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Pembayaran</h4>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <div class="flex items-start">
                                                <svg class="w-5 h-5 mr-2 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
                                                    <div class="space-y-1">
                                                        @foreach($paymentData as $key => $value)
                                                            @if($value)
                                                            <div class="flex justify-between text-sm">
                                                                <span class="text-blue-700">
                                                                    @if($key === 'piutang') 
                                                                        Piutang
                                                                    @elseif($key === 'jml_over') 
                                                                        Jml Over
                                                                    @elseif($key === 'od_30') 
                                                                        Jml OD 30
                                                                    @elseif($key === 'od_60') 
                                                                        Jml OD 60
                                                                    @elseif($key === 'od_90') 
                                                                        Jml OD 90
                                                                    @endif:
                                                                </span>
                                                                <span class="font-semibold text-blue-900">Rp {{ number_format($value, 0, ',', '.') }}</span>
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

                                    <!-- Notes Section -->
                                    @if($submission->revision_note || $submission->rejection_note)
                                    <div class="col-span-1 md:col-span-2">
                                        @if($submission->revision_note)
                                        <div class="text-sm text-orange-700 bg-orange-50 px-4 py-3 rounded-lg border-l-4 border-orange-500">
                                            <span class="font-semibold block mb-1">Catatan Revisi:</span>
                                            <p>{{ $submission->revision_note }}</p>
                                        </div>
                                        @endif
                                        @if($submission->rejection_note)
                                        <div class="text-sm text-red-700 bg-red-50 px-4 py-3 rounded-lg border-l-4 border-red-500 mt-2">
                                            <span class="font-semibold block mb-1">Alasan Penolakan:</span>
                                            <p>{{ $submission->rejection_note }}</p>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ request('search') || request('status') ? 'Tidak ada hasil yang cocok dengan filter' : 'Belum ada pengajuan' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination - Hanya tampil jika ada data dan merupakan paginator -->
        @if($submissions->count() > 0)
                @if(method_exists($submissions, 'hasPages') && $submissions->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200">
                    {{ $submissions->links() }}
                </div>
                @endif
            @endif
        </div>

        <!-- Summary Info - Hanya tampil jika ada data -->
        @if($submissions->count() > 0 && method_exists($submissions, 'total'))
        <div class="text-sm text-gray-600">
            Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} pengajuan
        </div>
        @endif
    @endif
</div>

@if(session('success'))
<div id="success-alert" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
</div>
@endif

<script>
/* Toggle Detail Row */
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

/* Handle View Change */
function handleViewChange() {
    const viewFilter = document.getElementById('viewFilter');
    const statusFilterWrapper = document.getElementById('statusFilterWrapper');
    const searchSubmissionWrapper = document.getElementById('searchSubmissionWrapper');
    const searchCustomerWrapper = document.getElementById('searchCustomerWrapper');
    
    if (viewFilter.value === 'submissions') {
        // Tampilkan filter untuk pengajuan
        statusFilterWrapper.classList.remove('hidden');
        searchSubmissionWrapper.classList.remove('hidden');
        searchCustomerWrapper.classList.add('hidden');
    } else {
        // Tampilkan search untuk customer
        statusFilterWrapper.classList.add('hidden');
        searchSubmissionWrapper.classList.add('hidden');
        searchCustomerWrapper.classList.remove('hidden');
    }
    
    // Submit form langsung ketika view berubah
    document.getElementById('filterForm').submit();
}

/* Auto-hide Success Message */
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.getElementById('success-alert');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    }
});
</script>
@endsection