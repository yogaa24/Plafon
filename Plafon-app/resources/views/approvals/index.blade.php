@extends('layouts.app')

@section('title', 'Dashboard Approval')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Approval Level {{ $level }}</h1>
        <p class="text-sm text-gray-600">Review dan proses pengajuan yang menunggu approval Anda</p>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('approvals.index') }}" class="space-y-4">
            <!-- Row 1: Search & Date Range -->
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, kios, atau sales..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date From -->
                <div class="w-44">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Dari Tanggal">
                </div>

                <!-- Date To -->
                <div class="w-44">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Sampai Tanggal">
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
                                <!-- Approve Button - SEMUA LEVEL PAKAI MODAL -->
                                @if($level == 2 && $submission->plafon_type === 'open')
                                    <!-- Level 2 Open Plafon: Modal dengan payment data -->
                                    <button onclick="openApprovalModal({{ $submission->id }}, 'approved')" 
                                            class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                            title="Setujui">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                @else
                                    <!-- Semua level lainnya: Modal dengan notes wajib -->
                                    <button onclick="openApprovalModalSimple({{ $submission->id }}, 'approved')" 
                                            class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                            title="Setujui">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Reject Button -->
                                <button onclick="openApprovalModal({{ $submission->id }}, 'rejected')" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition" title="Tolak">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>

                                <!-- Edit Komitmen Button (hanya Level 1, 2, 3) -->
                                @if(in_array($level, [1, 2, 3]))
                                <button onclick="openEditCommitmentModal({{ $submission->id }}, '{{ addslashes($submission->komitmen_pembayaran) }}')" 
                                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition" 
                                        title="Edit Komitmen">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
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

                                <!-- Previous Approvals -->
                                @if($submission->approvals->count() > 0)
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Riwayat Approval Sebelumnya</h4>
                                    <div class="space-y-2">
                                        @foreach($submission->approvals as $approval)
                                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm bg-green-500 text-white">
                                                    {{ $approval->level }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">{{ $approval->approver->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $approval->created_at->format('d M Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✓ Disetujui
                                            </span>
                                        </div>
                                        @if($approval->note)
                                        <div class="ml-11 text-xs text-gray-600 bg-gray-50 px-3 py-2 rounded">
                                            <span class="font-semibold">Catatan:</span> {{ $approval->note }}
                                        </div>
                                        @endif
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

                        <!-- Upload Lampiran -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Upload Lampiran <span class="text-gray-400">(Opsional)</span>
                            </label>
                            <input type="file" 
                                name="lampiran" 
                                id="lampiranInput" 
                                accept="image/jpeg,image/jpg,image/png" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                onchange="previewLampiranImage(event)">
                            <p class="text-xs text-gray-500 mt-1">
                                Format: JPG, JPEG, PNG. Maksimal 2MB
                            </p>
                            <!-- TAMBAHKAN IMAGE PREVIEW DI SINI -->
                            <div id="lampiranPreview" class="mt-3 hidden">
                                <div class="relative inline-block">
                                    <img id="lampiranPreviewImg" 
                                        class="max-w-xs max-h-64 rounded-lg border-2 border-gray-300 shadow-sm" 
                                        alt="Preview Lampiran">
                                    <button type="button" 
                                            onclick="removeLampiranPreview()" 
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition shadow-lg"
                                            title="Hapus gambar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Preview gambar yang akan diupload
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
                            <strong>💡 Info:</strong> Data di atas adalah data yang diisi oleh sales. Anda dapat memverifikasi dan mengubahnya jika diperlukan.
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan <span id="noteRequired" class="text-red-500">*</span>
                    </label>
                    <textarea id="approvalNote" name="note" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                              placeholder="Catatan wajib diisi untuk setiap tindakan..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">* Catatan wajib diisi untuk approve maupun reject</p>
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

<!-- Edit Komitmen Modal -->
<div id="editCommitmentModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-[2px] overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Komitmen Pembayaran</h3>
            
            <form id="editCommitmentForm" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="komitmenInput" name="komitmen_pembayaran" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: 30 hari">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeEditCommitmentModal()" 
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Simpan Perubahan
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
    
    // Get submission data
    const submission = submissionsData.find(s => s.id === submissionId);
    const jenisPembayaran = submission ? submission.payment_type : '';
    const paymentData = submission ? submission.payment_data : {};
    const plafonType = submission ? submission.plafon_type : '';
    const jmlOverInput = document.getElementById('jmlOverInput');

    /* RESET STATE SETIAP MODAL DIBUKA */
    jmlOverInput.readOnly = false;
    jmlOverInput.disabled = false;
    jmlOverInput.classList.remove('bg-gray-100');

    /* RULE BERDASARKAN JENIS PEMBAYARAN */
    if (jenisPembayaran === 'over') {
        // OVER → AUTO & KUNCI
        jmlOverInput.readOnly = true;
        jmlOverInput.classList.add('bg-gray-100');

        setupOverAutoCalculation(submissionId);
    } else {
        // OD → MANUAL
        jmlOverInput.readOnly = false;
        jmlOverInput.disabled = false;
        jmlOverInput.classList.remove('bg-gray-100');

        jmlOverInput.value = paymentData.jml_over || '';
    }

    
    // Set form action
    form.action = `/approvals/${submissionId}/process`;
    actionInput.value = action;
    
    // Set jenis pembayaran hidden input
    document.getElementById('jenisPembayaranInput').value = jenisPembayaran;
    
    // Show Level 2 fields only for approved action, level 2, AND Open Plafon
    if (action === 'approved' && currentLevel === 2 && plafonType === 'open') {
        level2Fields.classList.remove('hidden');
        
        // Display jenis pembayaran
        const jenisPembayaranDisplay = document.getElementById('jenisPembayaranDisplay');
        if (jenisPembayaran === 'over') {
            jenisPembayaranDisplay.innerHTML =
                '<span class="px-3 py-1 bg-purple-100 text-purple-700 rounded">OVER</span>';
        } else {
            jenisPembayaranDisplay.innerHTML =
                '<span class="px-3 py-1 bg-orange-100 text-orange-700 rounded">OD</span>';
        }
        
        // PRE-FILL data dari sales
        document.getElementById('piutangInput').value = paymentData.piutang || '';
        document.getElementById('jmlOverInput').value = paymentData.jml_over || '';
        document.getElementById('jmlOd30Input').value = paymentData.jml_od_30 || paymentData.od_30 || '';
        document.getElementById('jmlOd60Input').value = paymentData.jml_od_60 || paymentData.od_60 || '';
        document.getElementById('jmlOd90Input').value = paymentData.jml_od_90 || paymentData.od_90 || '';

        // ===== RULE JML OVER =====
        if (jenisPembayaran === 'over') {
            // OVER → auto hitung & tidak bisa diedit
            jmlOverInput.readOnly = true;
            jmlOverInput.classList.add('bg-gray-100');

            setupOverAutoCalculation(submissionId);
        } else {
            // OD → input manual
            jmlOverInput.readOnly = false;
            jmlOverInput.classList.remove('bg-gray-100');

            // ❗ hentikan auto calculation jika sebelumnya OVER
            jmlOverInput.value = paymentData.jml_over || '';
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

        // Required Level 2
        ['piutangInput', 'jmlOverInput', 'jmlOd30Input', 'jmlOd60Input', 'jmlOd90Input'].forEach(id => {
            document.getElementById(id).required = true;
        });
        
        const lampiranInput = document.getElementById('lampiranInput');
        
        modalTitle.textContent = 'Setujui Pengajuan - Verifikasi Data (Open Plafon)';
        approvalNote.placeholder = 'Catatan tambahan (wajib diisi)...';
        approvalNote.required = true;
        noteRequired.classList.remove('hidden');
        submitButton.className =
            'flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
        submitButton.textContent = 'Setujui';

    } else {
        level2Fields.classList.add('hidden');
        
        // Remove required
        ['piutangInput', 'jmlOverInput', 'jmlOd30Input', 'jmlOd60Input', 'jmlOd90Input'].forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.required = false;
        });
        
        const lampiranInput = document.getElementById('lampiranInput');
        if (lampiranInput) lampiranInput.required = false;
        
        // Reject only
        if (action === 'rejected') {
            modalTitle.textContent = 'Tolak Pengajuan';
            approvalNote.placeholder = 'Jelaskan alasan penolakan...';
            approvalNote.required = true;
            noteRequired.classList.remove('hidden');
            submitButton.className =
                'flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition';
            submitButton.textContent = 'Tolak';
        }
    }
    
    // Clear note
    approvalNote.value = '';
    
    modal.classList.remove('hidden');
}


function previewLampiranImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('lampiranPreviewImg');
    const previewContainer = document.getElementById('lampiranPreview');
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB');
            event.target.value = '';
            previewContainer.classList.add('hidden');
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file harus JPG, JPEG, atau PNG');
            event.target.value = '';
            previewContainer.classList.add('hidden');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.classList.add('hidden');
    }
}

// Function untuk menghapus preview dan reset input file
function removeLampiranPreview() {
    const input = document.getElementById('lampiranInput');
    const preview = document.getElementById('lampiranPreviewImg');
    const previewContainer = document.getElementById('lampiranPreview');
    
    input.value = '';
    preview.src = '';
    previewContainer.classList.add('hidden');
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
        showOverWarning(jmlOver);
    }

    piutangInput.addEventListener('input', calculateJmlOver);
    piutangInput.addEventListener('change', calculateJmlOver);

    if (piutangInput.value) {
        calculateJmlOver();
    }
}


// Optional: Show warning jika Jml Over negatif
function showOverWarning(jmlOver) {
    let warningDiv = document.getElementById('overWarningModal');
    
    if (!warningDiv) {
        // Create warning div jika belum ada
        warningDiv = document.createElement('div');
        warningDiv.id = 'overWarningModal';
        warningDiv.className = 'mt-2 p-2 rounded text-xs';
        document.getElementById('jmlOverInput').parentNode.appendChild(warningDiv);
    }
    
    if (jmlOver < 0) {
        warningDiv.className = 'mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700';
        warningDiv.innerHTML = `
            <strong>⚠️ Perhatian:</strong> Jml Over negatif (${new Intl.NumberFormat('id-ID').format(jmlOver)}). 
            Customer melebihi plafon sebesar Rp ${new Intl.NumberFormat('id-ID').format(Math.abs(jmlOver))}
        `;
    } else {
        warningDiv.className = 'mt-2 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-700';
        warningDiv.innerHTML = `
            Sisa plafon tersedia Rp ${new Intl.NumberFormat('id-ID').format(jmlOver)}
        `;
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
    
    // Hide level 2 fields
    level2Fields.classList.add('hidden');
    
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
    
    // TAMBAHKAN INI - Remove warning
    const warningDiv = document.getElementById('overWarningModal');
    if (warningDiv) {
        warningDiv.remove();
    }
}

function openEditCommitmentModal(submissionId, currentCommitment) {
    const modal = document.getElementById('editCommitmentModal');
    const form = document.getElementById('editCommitmentForm');
    const input = document.getElementById('komitmenInput');
    
    form.action = `/approvals/${submissionId}/update-commitment`;
    input.value = currentCommitment;
    
    modal.classList.remove('hidden');
}

function closeEditCommitmentModal() {
    const modal = document.getElementById('editCommitmentModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('approvalModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});

document.getElementById('editCommitmentModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditCommitmentModal();
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

document.getElementById('approvalForm').addEventListener('submit', function (e) {
    const jmlOverInput = document.getElementById('jmlOverInput');

    // Jika field Jml Over ada (khusus Level 2 Open Plafon)
    if (jmlOverInput && !jmlOverInput.closest('.hidden')) {
        const jmlOver = parseFloat(jmlOverInput.value) || 0;

        if (jmlOver < 0) {
            e.preventDefault(); // 🚫 STOP submit

            alert(
                '❌ Tidak bisa disimpan!\n\n' +
                'Jml Over bernilai minus.\n' +
                'Customer melebihi plafon.\n\n' +
                'Silakan perbaiki nilai Piutang atau data terkait.'
            );

            jmlOverInput.focus();
            return false;
        }
    }
});

</script>
@endsection