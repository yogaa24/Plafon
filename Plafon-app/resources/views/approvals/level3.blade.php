@extends('layouts.app')

@section('title', 'Dashboard Approval')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Approval Kabag</h1>
            <p class="text-sm text-gray-600">Review dan proses pengajuan yang menunggu approval Anda</p>
        </div>
        
        <!-- Export Button (hanya untuk Approver Level 3) -->
        @if(auth()->user()->role === 'approver3')
            <a href="{{ route('approvals.level3.export', request()->query()) }}"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        @endif
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('approvals.level3') }}" id="filterForm" class="space-y-4">
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
                    <a href="{{ route('approvals.level3') }}" 
                       class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
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
                                <button type="button" onclick="toggleDetail({{ $submission->id }})" 
                                        class="text-gray-500 hover:text-indigo-600 focus:outline-none transition" title="Lihat Detail">
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
                                    <span class="text-xs text-gray-400 line-through">
                                        {{ number_format($submission->customer->plafon_aktif, 0, ',', '.') }}
                                    </span>
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
                            <div class="flex items-center justify-center space-x-1">
                                @for($i = 1; $i < 3; $i++)
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
                                    3
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Approve Button -->
                                <button onclick="openApprovalModal({{ $submission->id }}, 'approved')" 
                                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition" 
                                        title="Setujui">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                
                                <!-- Reject Button -->
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
                        <td colspan="10" class="px-4 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white rounded-lg border border-gray-200">
                                <!-- Informasi Umum -->
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
                                        @if($submission->keterangan)
                                        <div class="py-1 border-t border-gray-100 mt-2 pt-2">
                                            <span class="text-sm text-gray-600 block mb-1">Keterangan:</span>
                                            <span class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $submission->keterangan }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Informasi Keuangan -->
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
                                            <span class="text-sm text-gray-600">Jumlah Value Faktur:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Sales:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $submission->sales->name }}</span>
                                        </div>
                                        <div class="py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600 block mb-1">Komitmen Pembayaran:</span>
                                            <span class="text-sm text-gray-900 font-medium">{{ $submission->komitmen_pembayaran }}</span>
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
                                                                src="{{ Storage::url($path) }}"
                                                                alt="Lampiran"
                                                                onclick="openImageModal('{{ Storage::url($path) }}')"
                                                                class="w-full h-32 object-cover rounded-lg border-2 border-gray-300
                                                                    hover:border-indigo-500 transition cursor-pointer"
                                                            >
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
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
                        <td colspan="10" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586 a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pengajuan</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ request('search') ? 'Tidak ada hasil yang cocok dengan filter' : 'Belum ada pengajuan yang menunggu approval Anda' }}
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
            {{ $submissions->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Info -->
    <div class="text-sm text-gray-600">
        Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} pengajuan
    </div>
</div>


<!-- Approval Modal (NOTES WAJIB) -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-[2px] overflow-y-auto h-full w-full z-50 
    transition-opacity duration-200 opacity-0">
    <div id="approvalModalContent" class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white 
        transform transition-all duration-300 -translate-y-12 scale-95 opacity-0">
        <div class="mt-3">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4"></h3>
            
            <form id="approvalForm" method="POST">
                @csrf
                <input type="hidden" name="action" id="actionInput" value="">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="approvalNote" name="note" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                              placeholder="Catatan wajib diisi untuk setiap tindakan..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">* Catatan wajib diisi untuk approve maupun reject</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeApprovalModal()" 
                            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" id="submitButton" 
                            class="flex-1 px-4 py-2 text-white rounded-lg transition">
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
const currentLevel = 3;
const submissionsData = @json($submissionsArray);

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

function openApprovalModal(submissionId, action) {
    const modal = document.getElementById('approvalModal');
    const modalContent = document.getElementById('approvalModalContent');
    const modalTitle = document.getElementById('modalTitle');
    const approvalNote = document.getElementById('approvalNote');
    const submitButton = document.getElementById('submitButton');
    const form = document.getElementById('approvalForm');
    const actionInput = document.getElementById('actionInput');
    
    form.action = `/approvals/${submissionId}/process`;
    actionInput.value = action;
    
    if (action === 'approved') {
        modalTitle.textContent = 'Setujui Pengajuan';
        approvalNote.placeholder = 'Jelaskan alasan persetujuan Anda...';
        submitButton.className = 'flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition';
        submitButton.textContent = 'Setujui';
    } else {
        modalTitle.textContent = 'Tolak Pengajuan';
        approvalNote.placeholder = 'Jelaskan alasan penolakan Anda...';
        submitButton.className = 'flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition';
        submitButton.textContent = 'Tolak';
    }
    
    approvalNote.value = '';
    
    // Show modal (remove hidden first)
    modal.classList.remove('hidden');
    
    // Trigger animation with slight delay
    setTimeout(() => {
        // Fade in overlay
        modal.classList.remove('opacity-0');
        modal.classList.add('opacity-100');
        
        // Slide down + scale modal content
        modalContent.classList.remove('-translate-y-12', 'scale-95', 'opacity-0');
        modalContent.classList.add('translate-y-0', 'scale-100', 'opacity-100');
    }, 10);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeApprovalModal() {
    const modal = document.getElementById('approvalModal');
    const modalContent = document.getElementById('approvalModalContent');
    
    // NO ANIMATION - Langsung hide
    modal.classList.add('hidden');
    
    // Reset classes untuk next time
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');
    
    modalContent.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
    modalContent.classList.add('-translate-y-12', 'scale-95', 'opacity-0');
    
    // Re-enable body scroll
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.getElementById('approvalModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('approvalModal');
        if (!modal.classList.contains('hidden')) {
            closeApprovalModal();
        }
    }
});

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

// Close when clicking outside image
document.getElementById('imageModal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Close with ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection