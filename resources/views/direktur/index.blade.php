@extends('layouts.app')

@section('title', 'Dashboard Pengajuan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                    Executive Overview
                </span>
                <span class="text-xs text-gray-500">• Seluruh Proses Bisnis</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-1">
                Dashboard Pengajuan
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Monitoring pengajuan plafon customers
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('direktur.export', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('direktur.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm"
               title="Refresh data">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Segarkan
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Eksekutif -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Card 1: Total Pengajuan -->
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pengajuan</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 truncate">Nilai: Rp {{ number_format($stats['total_nominal_plafon'], 0, ',', '.') }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500"></div>
        </div>

        <!-- Card 2: Sedang Diproses -->
        <a href="{{ route('direktur.index', array_merge(request()->query(), ['status' => 'in_progress'])) }}" 
           class="bg-white rounded-xl p-4 border border-gray-200 shadow-xs hover:border-amber-300 transition relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Sedang Diproses</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['in_progress']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-amber-600 font-medium mt-2">Dalam antrean workflow</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500"></div>
        </a>

        <!-- Card 3: Selesai (Done) -->
        <a href="{{ route('direktur.index', array_merge(request()->query(), ['status' => 'done'])) }}" 
           class="bg-white rounded-xl p-4 border border-gray-200 shadow-xs hover:border-green-300 transition relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">Selesai (Done)</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['done']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-green-700 font-medium mt-2 truncate">Rp {{ number_format($stats['done_nominal_plafon'], 0, ',', '.') }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-green-500"></div>
        </a>

        <!-- Card 4: Ditolak -->
        <a href="{{ route('direktur.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
           class="bg-white rounded-xl p-4 border border-gray-200 shadow-xs hover:border-rose-300 transition relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Ditolak</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['rejected']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-rose-600 mt-2">Tidak disetujui</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500"></div>
        </a>

        <!-- Card 5: Perlu Revisi Sales -->
        <a href="{{ route('direktur.index', array_merge(request()->query(), ['status' => 'revision'])) }}" 
           class="bg-white rounded-xl p-4 border border-gray-200 shadow-xs hover:border-orange-300 transition relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-orange-600 uppercase tracking-wider">Perlu Revisi</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['revision']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-orange-600 mt-2">Kembali ke Sales</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-orange-500"></div>
        </a>
    </div>

    <!-- Filter & Pencarian Lengkap -->
    <div class="bg-white rounded-xl shadow-xs p-4 md:p-5 border border-gray-200">
        <form method="GET" action="{{ route('direktur.index') }}" id="filterForm" class="space-y-4">
            <!-- Row 1: Search, Status, Plafon Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Input -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Kata Kunci</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Kode, customer, kios, sales..." 
                               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status / Tahapan Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status / Tahap Approval</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">Semua Status (Keseluruhan)</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Diproses (Semua Tahap)</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Manager SC (Level 1)</option>
                        <option value="approved_1" {{ request('status') === 'approved_1' ? 'selected' : '' }}>Menunggu Collection (Level 2)</option>
                        <option value="approved_2" {{ request('status') === 'approved_2' ? 'selected' : '' }}>Menunggu Manager Keuangan (Level 3)</option>
                        <option value="approved_3" {{ request('status') === 'approved_3' ? 'selected' : '' }}>Menunggu Kadep Keu & Sales (Level 4)</option>
                        <option value="direksi" {{ request('status') === 'direksi' ? 'selected' : '' }}>Menunggu Direksi (Level 5 & 6)</option>
                        <option value="pending_viewer" {{ request('status') === 'pending_viewer' ? 'selected' : '' }}>Proses Input (Viewer)</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Selesai Disetujui (Done)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                        <option value="revision" {{ request('status') === 'revision' ? 'selected' : '' }}>Perlu Revisi Sales (Revision)</option>
                    </select>
                </div>

                <!-- Jenis Plafon Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Plafon</label>
                    <select name="plafon_type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Jenis (Open & Rubah)</option>
                        <option value="open" {{ request('plafon_type') === 'open' ? 'selected' : '' }}>Open Plafon</option>
                        <option value="rubah" {{ request('plafon_type') === 'rubah' ? 'selected' : '' }}>Rubah Plafon</option>
                    </select>
                </div>

                <!-- Status Target Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status Target</label>
                    <select name="target_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status Target</option>
                        <option value="masuk_target" {{ request('target_status') === 'masuk_target' ? 'selected' : '' }}>Masuk Target</option>
                        <option value="tidak_masuk_target" {{ request('target_status') === 'tidak_masuk_target' ? 'selected' : '' }}>Tidak Masuk Target</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Sales, Dates, Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <!-- Sales Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sales PIC</label>
                    <select name="sales_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Sales</option>
                        @foreach($salesList as $sales)
                            <option value="{{ $sales->id }}" {{ request('sales_id') == $sales->id ? 'selected' : '' }}>
                                {{ $sales->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-xs transition">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('direktur.index') }}" 
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition"
                       title="Reset filter">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Pemantauan Semua Pengajuan -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Daftar Pengajuan Sistem</h2>
                <p class="text-xs text-gray-500">Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} total data</p>
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span> Disetujui
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 ml-2"></span> Menunggu
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 ml-2"></span> Ditolak
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3.5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-3.5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal & Kode</th>
                        <th class="px-3.5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer & Kios</th>
                        <th class="px-3.5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sales</th>
                        <th class="px-3.5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Plafon</th>
                        <th class="px-3.5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Jenis</th>
                        <th class="px-3.5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status / Tahap</th>
                        <th class="px-3.5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Workflow Tracker</th>
                        <th class="px-3.5 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($submissions as $index => $submission)
                    <tr class="hover:bg-indigo-50/30 transition">
                        <!-- No -->
                        <td class="px-3.5 py-3 text-center text-xs font-medium text-gray-600">
                            {{ $submissions->firstItem() + $index }}
                        </td>

                        <!-- Tanggal & Kode -->
                        <td class="px-3.5 py-3 whitespace-nowrap">
                            <div class="text-xs font-bold text-indigo-700 tracking-wide font-mono">
                                {{ $submission->kode }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                {{ $submission->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>

                        <!-- Customer & Kios -->
                        <td class="px-3.5 py-3">
                            <div class="text-sm font-semibold text-gray-900 leading-tight">
                                {{ $submission->nama }}
                            </div>
                            <div class="text-xs text-gray-600 mt-0.5 flex items-center gap-1">
                                <span class="font-medium text-gray-800">{{ $submission->nama_kios }}</span>
                                @if(optional($submission->customer)->kode_customer)
                                    <span class="text-[11px] text-gray-400">({{ $submission->customer->kode_customer }})</span>
                                @endif
                            </div>
                        </td>

                        <!-- Sales -->
                        <td class="px-3.5 py-3 whitespace-nowrap">
                            <span class="text-xs font-medium text-gray-900 bg-gray-100 px-2 py-0.5 rounded">
                                {{ optional($submission->sales)->name ?? 'N/A' }}
                            </span>
                        </td>

                        <!-- Plafon -->
                        <td class="px-3.5 py-3 whitespace-nowrap">
                            @if($submission->plafon_type === 'rubah' && $submission->plafon_sebelumnya)
                                <div class="flex flex-col">
                                    <span class="text-[11px] text-gray-400 line-through">
                                        Rp {{ number_format($submission->plafon_sebelumnya, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-bold {{ $submission->plafon > $submission->plafon_sebelumnya ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($submission->plafon, 0, ',', '.') }}
                                    </span>
                                </div>
                            @else
                                <div class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($submission->plafon, 0, ',', '.') }}
                                </div>
                                @if($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur)
                                    <div class="text-[11px] text-gray-500">
                                        Faktur: Rp {{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        <!-- Jenis -->
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            {!! $submission->plafon_type_badge !!}
                        </td>

                        <!-- Status / Tahap Saat Ini -->
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            <div>
                                {!! $submission->status_badge !!}
                            </div>
                            @if($submission->rejection_note)
                                <div class="text-[11px] text-rose-600 mt-1 max-w-[170px] truncate mx-auto" title="{{ $submission->rejection_note }}">
                                    Catatan: {{ $submission->rejection_note }}
                                </div>
                            @endif
                        </td>

                        <!-- Workflow Tracker (1 s/d 6 + Input) -->
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            @php
                                $maxLevel = $submission->approvals->max('level') ?? 0;
                            @endphp
                            <div class="flex items-center justify-center gap-1">
                                @for($i = 1; $i <= 6; $i++)
                                    @php
                                        $appr = $submission->approvals->where('level', $i)->sortByDesc('created_at')->first();
                                    @endphp
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold transition
                                        @if($appr && $appr->status === 'approved')
                                            bg-green-500 text-white
                                        @elseif($appr && in_array($appr->status, ['rejected', 'revision']))
                                            bg-red-500 text-white
                                        @elseif($submission->current_level == $i && !in_array($submission->status, ['done', 'rejected']))
                                            bg-amber-400 text-white animate-pulse
                                        @else
                                            bg-gray-200 text-gray-400
                                        @endif" 
                                        title="Level {{ $i }}: {{ $appr ? ucfirst($appr->status) : ($submission->current_level == $i ? 'Sedang Menunggu' : 'Belum Diproses') }}">
                                        {{ $i }}
                                    </div>
                                @endfor
                                <!-- Viewer Check -->
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                                    {{ $submission->status === 'done' ? 'bg-green-600 text-white' : ($submission->status === 'pending_viewer' ? 'bg-teal-500 text-white animate-pulse' : 'bg-gray-200 text-gray-400') }}"
                                    title="Viewer/Input: {{ $submission->status === 'done' ? 'Selesai' : ($submission->status === 'pending_viewer' ? 'Sedang Input' : 'Belum') }}">
                                    ✓
                                </div>
                            </div>
                        </td>

                        <!-- Aksi: Detail Toggle -->
                        <td class="px-3.5 py-3 text-center whitespace-nowrap">
                            <button type="button" 
                                    onclick="toggleDetail({{ $submission->id }})" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition focus:outline-none">
                                <span>Detail</span>
                                <svg id="icon-{{ $submission->id }}" class="w-3.5 h-3.5 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    <!-- Baris Accordion Detail Lengkap Pengajuan -->
                    <tr id="detail-{{ $submission->id }}" class="hidden bg-slate-50/80">
                        <td colspan="9" class="px-4 py-5">
                            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs space-y-6">
                                <!-- Top Bar Informasi Pengajuan -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b border-gray-100 gap-2">
                                    <div>
                                        <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                            {{ $submission->kode }}
                                        </span>
                                        <span class="ml-2 text-sm font-bold text-gray-900">
                                            {{ $submission->nama }} - {{ $submission->nama_kios }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        {!! $submission->plafon_type_badge !!}
                                        {!! $submission->status_badge !!}
                                    </div>
                                </div>

                                <!-- Grid Informasi Utama: Informasi Umum & Keuangan -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Kolom 1: Informasi Customer & Pengajuan -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider pb-1 border-b border-gray-100 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Informasi Customer & Usaha
                                        </h4>
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Nama Customer:</span>
                                                <span class="font-semibold text-gray-900">{{ $submission->nama }}</span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Nama Kios/Toko:</span>
                                                <span class="font-semibold text-gray-900">{{ $submission->nama_kios }}</span>
                                            </div>
                                            @if(optional($submission->customer)->kode_customer)
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Kode Customer:</span>
                                                <span class="font-semibold text-gray-900">{{ $submission->customer->kode_customer }}</span>
                                            </div>
                                            @endif
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Sales PIC:</span>
                                                <span class="font-semibold text-indigo-700">{{ optional($submission->sales)->name ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Waktu Pengajuan:</span>
                                                <span class="font-semibold text-gray-800">{{ $submission->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                            <div class="py-1">
                                                <span class="text-gray-500 block mb-0.5">Alamat:</span>
                                                <span class="text-gray-900 font-medium bg-gray-50 p-2 rounded block">{{ $submission->alamat ?: '-' }}</span>
                                            </div>
                                            @if($submission->keterangan)
                                            <div class="py-1">
                                                <span class="text-gray-500 block mb-0.5">Keterangan Sales:</span>
                                                <span class="text-gray-900 italic bg-gray-50 p-2 rounded block">"{{ $submission->keterangan }}"</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Kolom 2: Informasi Plafon & Finansial -->
                                    <div class="space-y-3">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider pb-1 border-b border-gray-100 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Nilai Plafon & Target
                                        </h4>
                                        <div class="space-y-2 text-xs">
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Jenis Pengajuan:</span>
                                                <span class="font-bold text-gray-900">
                                                    {{ $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon' }}
                                                    @if($submission->plafon_direction)
                                                        ({{ $submission->plafon_direction === 'naik' ? '↑ Naik' : '↓ Turun' }})
                                                    @endif
                                                </span>
                                            </div>
                                            @if($submission->plafon_type === 'rubah' && $submission->plafon_sebelumnya)
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Plafon Sebelumnya:</span>
                                                <span class="font-medium text-gray-600">Rp {{ number_format($submission->plafon_sebelumnya, 0, ',', '.') }}</span>
                                            </div>
                                            @endif
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Plafon Yang Diajukan:</span>
                                                <span class="font-bold text-emerald-700 text-sm">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                                            </div>
                                            @if($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur)
                                            <div class="flex justify-between py-1 border-b border-gray-50">
                                                <span class="text-gray-500">Jumlah Buka Faktur:</span>
                                                <span class="font-semibold text-gray-900">Rp {{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</span>
                                            </div>
                                            @endif
                                            <div class="flex justify-between py-1 border-b border-gray-50 items-center">
                                                <span class="text-gray-500">Klasifikasi Target:</span>
                                                <span>{!! $submission->target_status_badge ?: '<span class="text-gray-400">Belum Ditentukan</span>' !!}</span>
                                            </div>
                                        </div>

                                        <!-- Komitmen Pembayaran (Partial Komitmen History) -->
                                        <div class="pt-2">
                                            @include('partials.komitmen-history', ['submission' => $submission])
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi OD / Over (Collection) jika ada -->
                                @if($submission->payment_type && $submission->payment_data)
                                    @php
                                        $paymentData = is_array($submission->payment_data) ? $submission->payment_data : json_decode($submission->payment_data, true);
                                    @endphp
                                    <div class="p-3.5 bg-blue-50/70 rounded-xl border border-blue-200">
                                        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Data Piutang & Over Limit (Diisi oleh Team Collection)
                                        </h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                                            <div class="bg-white p-2.5 rounded-lg border border-blue-100">
                                                <span class="text-gray-500 block">Piutang:</span>
                                                <span class="font-bold text-blue-900">Rp {{ number_format($paymentData['piutang'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="bg-white p-2.5 rounded-lg border border-blue-100">
                                                <span class="text-gray-500 block">Jml Over:</span>
                                                <span class="font-bold text-rose-700">Rp {{ number_format($paymentData['jml_over'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="bg-white p-2.5 rounded-lg border border-blue-100">
                                                <span class="text-gray-500 block">OD 30 Hari:</span>
                                                <span class="font-semibold text-gray-800">Rp {{ number_format($paymentData['od_30'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="bg-white p-2.5 rounded-lg border border-blue-100">
                                                <span class="text-gray-500 block">OD 60 Hari:</span>
                                                <span class="font-semibold text-gray-800">Rp {{ number_format($paymentData['od_60'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="bg-white p-2.5 rounded-lg border border-blue-100">
                                                <span class="text-gray-500 block">OD 90 Hari:</span>
                                                <span class="font-semibold text-gray-800">Rp {{ number_format($paymentData['od_90'] ?? 0, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Lampiran Foto / Dokumen -->
                                @if($submission->lampiran_path)
                                    @php
                                        $lampiranPaths = is_array($submission->lampiran_path) ? $submission->lampiran_path : json_decode($submission->lampiran_path, true);
                                    @endphp
                                    @if($lampiranPaths && count($lampiranPaths) > 0)
                                        <div class="pt-2">
                                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                Lampiran Berkas ({{ count($lampiranPaths) }} foto)
                                            </h4>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
                                                @foreach($lampiranPaths as $path)
                                                    <div class="group relative aspect-video bg-gray-100 rounded-lg overflow-hidden border border-gray-200 cursor-pointer shadow-2xs hover:shadow-md transition"
                                                         onclick="openImageModal('{{ asset($path) }}')">
                                                        <img src="{{ asset($path) }}" alt="Lampiran" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-medium">
                                                            Lihat
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <!-- Jejak Audit Trail & Riwayat Approval Seluruh Level -->
                                <div class="pt-3 border-t border-gray-100">
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Audit Trail & Riwayat Persetujuan Lengkap
                                    </h4>

                                    @if($submission->approvals && $submission->approvals->count() > 0)
                                        <div class="space-y-2.5">
                                            @foreach($submission->approvals->sortBy('created_at') as $approval)
                                                @php
                                                    $isAppr = $approval->status === 'approved';
                                                    $isRev = in_array($approval->status, ['revision', 'returned_to_collection']) || str_contains($approval->note ?? '', 'Komitmen');
                                                    $isRej = $approval->status === 'rejected';
                                                    
                                                    $levelRoleName = match($approval->level) {
                                                        1 => 'Level 1: Manager SC',
                                                        2 => 'Level 2: Team Collection',
                                                        3 => 'Level 3: Manager Keuangan',
                                                        4 => 'Level 4: Kadep Keu & Sales',
                                                        5 => 'Level 5: Direktur Operasional',
                                                        6 => 'Level 6: Direktur Utama',
                                                        default => 'Level ' . $approval->level
                                                    };
                                                @endphp
                                                <div class="p-3 rounded-lg border text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2
                                                    {{ $isAppr ? 'bg-green-50/60 border-green-200' : '' }}
                                                    {{ $isRev ? 'bg-amber-50/60 border-amber-200' : '' }}
                                                    {{ $isRej ? 'bg-rose-50/60 border-rose-200' : '' }}">
                                                    
                                                    <div class="flex items-start gap-2.5">
                                                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-white shrink-0
                                                            {{ $isAppr ? 'bg-green-600' : ($isRev ? 'bg-amber-500' : 'bg-rose-600') }}">
                                                            {{ $approval->level }}
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-bold text-gray-900">{{ $levelRoleName }}</span>
                                                                <span class="text-gray-400">•</span>
                                                                <span class="text-gray-600">{{ optional($approval->approver)->name ?? 'User' }}</span>
                                                            </div>
                                                            <div class="text-[11px] text-gray-500 mt-0.5">
                                                                {{ $approval->created_at->format('d M Y, H:i:s') }}
                                                            </div>
                                                            @if($approval->note)
                                                                <div class="mt-1.5 p-2 bg-white rounded border border-gray-200/80 text-gray-800">
                                                                    <span class="font-semibold text-gray-600">Catatan:</span>
                                                                    <span>"{{ $approval->note }}"</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="shrink-0 self-start sm:self-center">
                                                        <span class="px-2.5 py-1 rounded-full font-bold text-[11px]
                                                            {{ $isAppr ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                                            {{ $isRev ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                                            {{ $isRej ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}">
                                                            @if($isAppr) Disetujui
                                                            @elseif($isRev) Revisi / Ditolak Komitmen
                                                            @elseif($isRej) Ditolak
                                                            @else {{ ucfirst($approval->status) }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 italic py-2">Belum ada riwayat approval tercatat pada pengajuan ini.</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">Tidak ada data pengajuan yang sesuai</p>
                            <p class="text-xs text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($submissions->hasPages())
        <div class="px-5 py-4 border-t border-gray-200 bg-white">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Zoom Lampiran Gambar -->
<div id="imageModal" class="hidden fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4">
    <div class="relative max-w-4xl w-full">
        <button onclick="closeImageModal()" 
                class="absolute -top-3 -right-3 bg-red-600 hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition focus:outline-none ring-2 ring-white cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="imageModalContent" src="" alt="Preview Lampiran" 
             class="w-full max-h-[85vh] object-contain rounded-lg shadow-2xl bg-white">
    </div>
</div>

<script>
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

// Close image modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Close when clicking modal backdrop
document.getElementById('imageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>
@endsection
