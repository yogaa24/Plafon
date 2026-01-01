@extends('layouts.app')

@section('title', 'Dashboard Viewer')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Viewer</h1>
            <p class="text-sm text-gray-600">Pengajuan menunggu pengecekan & yang telah diselesaikan</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="openImportModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold text-sm hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV
            </button>
            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold text-sm">
                ✓ {{ $pendingCount }} Perlu Input
            </span>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('viewer.index') }}" class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, kios, atau sales..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Sales Filter -->
                <div class="w-52">
                    <select name="sales_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <!-- Date To -->
                <div class="w-44">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <button type="submit" class="px-5 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                    Filter
                </button>
                <a href="{{ route('viewer.index') }}" class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                    Reset
                </a>
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Jumlah Buka (Rp.)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Jenis</th>
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
                                <button type="button" onclick="toggleDetail({{ $submission->id }})" class="text-gray-500 hover:text-green-600 focus:outline-none transition" title="Lihat Detail">
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
                                        {{ number_format($submission->plafon_sebelumnya, 0, ',', '.') }}
                                    </span>
                                    <!-- Plafon Baru -->
                                    <span class="text-sm font-semibold {{ $submission->plafon > $submission->plafon_sebelumnya ? 'text-green-600' : 'text-red-600' }}">
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
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center space-x-1">
                                @php
                                    $maxLevel = $submission->approvals->max('level') ?? 0;
                                @endphp
                                @for($i = 1; $i <= $maxLevel; $i++)
                                    @php
                                        $approval = $submission->approvals->where('level', $i)->first();
                                    @endphp
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                        @if($approval && $approval->status == 'approved') bg-green-500 text-white
                                        @elseif($approval && $approval->status == 'rejected') bg-red-500 text-white
                                        @else bg-gray-200 text-gray-500
                                        @endif">
                                        {{ $i }}
                                    </div>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <!-- Tombol Done -->
                                @if($submission->status === 'pending_viewer')
                                <form action="{{ route('viewer.done', $submission) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menandai sebagai Selesai?')">
                                    @csrf
                                    <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                                        Done
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Detail Row (Hidden by default) -->
                    <tr id="detail-{{ $submission->id }}" class="hidden bg-green-50">
                        <td colspan="10" class="px-4 py-4">
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
                                         @if($submission->keterangan)
                                        <div class="py-1 border-t border-gray-100 mt-2 pt-2">
                                            <span class="text-sm text-gray-600 block mb-1">Keterangan:</span>
                                            <span class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $submission->keterangan }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Keuangan</h4>
                                    <div class="space-y-2">
                                        @if($submission->plafon_type === 'rubah' && $submission->customer)
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon Sebelumnya:</span>
                                            <span class="text-sm text-gray-500">Rp {{ number_format($submission->plafon_sebelumnya, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Plafon {{ $submission->plafon_type === 'rubah' ? 'Baru' : '' }}:</span>
                                            <span class="text-sm font-bold text-green-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                                        </div>
                                        @if($submission->plafon_type === 'open')
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Jumlah Buka Faktur (Rp.)</span>
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
                                        <!-- Lampiran Gambar Section -->
                                        @if($submission->lampiran_path)
                                        <div class="col-span-1 md:col-span-2">
                                            <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Lampiran</h4>
                                            <div class=" rounded-lg p-4">
                                                <a href="{{ Storage::url($submission->lampiran_path) }}" target="_blank" class="inline-block">
                                                    <img src="{{ Storage::url($submission->lampiran_path) }}" 
                                                        alt="Lampiran Pengajuan" 
                                                        class="w-30 max-h-30 object-contain rounded-lg border-2 border-gray-300 hover:border-blue-500 transition cursor-pointer shadow hover:shadow-lg">
                                                </a>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                    </svg>
                                                    Klik gambar untuk melihat ukuran penuh
                                                </p>
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
                                <!-- Riwayat Approval -->
                                @if($submission->approvals->count() > 0)
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                                        Riwayat Approval
                                    </h4>

                                    <div class="space-y-3">
                                        @foreach($submission->approvals->sortBy('level') as $approval)
                                        @php
                                            $isApproved = $approval->status === 'approved';
                                            $isRejected = $approval->status === 'rejected';
                                        @endphp

                                        <div class="flex items-start justify-between p-4 rounded-lg border
                                            {{ $isApproved ? 'bg-green-50 border-green-200' : '' }}
                                            {{ $isRejected ? 'bg-red-50 border-red-200' : '' }}
                                        ">
                                            <!-- KIRI -->
                                            <div class="flex items-start space-x-3">
                                                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm text-white
                                                    {{ $isApproved ? 'bg-green-500' : '' }}
                                                    {{ $isRejected ? 'bg-red-500' : '' }}
                                                ">
                                                    {{ $approval->level }}
                                                </div>

                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">
                                                        {{ $approval->approver->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $approval->created_at->format('d M Y H:i') }}
                                                    </p>

                                                    @if($approval->note)
                                                        <div class="mt-2 p-2 bg-white border border-gray-200 rounded">
                                                            <p class="text-xs font-semibold text-gray-500 mb-1">
                                                                {{ $isRejected ? 'Alasan Penolakan:' : 'Catatan:' }}
                                                            </p>
                                                            <p class="text-xs text-gray-700 italic">
                                                                "{{ $approval->note }}"
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- KANAN (STATUS) -->
                                            <span class="text-xs px-2 py-1 rounded-full font-semibold h-fit
                                                {{ $isApproved ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $isRejected ? 'bg-red-100 text-red-700' : '' }}
                                            ">
                                                {{ $isApproved ? '✓ Disetujui' : '✕ Ditolak' }}
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ request()->anyFilled(['search', 'sales_id', 'date_from', 'date_to']) ? 'Tidak ada hasil yang cocok dengan filter' : 'Belum ada pengajuan yang selesai' }}</p>
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
    <div class="flex justify-between items-center text-sm text-gray-600">
        <div>
            Menampilkan {{ $submissions->firstItem() ?? 0 }} - {{ $submissions->lastItem() ?? 0 }} dari {{ $submissions->total() }} pengajuan selesai
        </div>
    </div>
</div>

<!-- Import CSV Modal - Updated Version -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Import Data Customer dari CSV</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Upload Form -->
        <form action="{{ route('viewer.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih File CSV <span class="text-red-500">*</span>
                </label>
                <input type="file" name="csv_file" accept=".csv" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">File harus berformat .csv dengan maksimal 5MB</p>
            </div>

            <div id="importProgress" class="hidden mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%" id="progressBar"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2 text-center" id="progressText">Memproses...</p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeImportModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Alert Messages - Updated untuk menampilkan error detail -->
@if(session('success'))
<div id="success-alert" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <div>{{ session('success') }}</div>
    </div>
</div>
@endif

@if(session('warning'))
<div id="warning-alert" class="fixed bottom-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p>{{ session('warning') }}</p>
            @if(session('error_details'))
            <button onclick="toggleErrorDetails()" class="text-xs underline mt-1">Lihat Detail Error</button>
            <pre id="errorDetails" class="hidden text-xs mt-2 p-2 bg-yellow-600 rounded overflow-auto max-h-40">{{ session('error_details') }}</pre>
            @endif
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div id="error-alert" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>{{ session('error') }}</div>
    </div>
</div>
@endif

@if($errors->any())
<div id="validation-alert" class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md">
    <div class="flex items-start">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>
            <p class="font-semibold mb-1">Validasi Error:</p>
            <ul class="text-sm space-y-1">
                @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

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

function toggleErrorDetails() {
    const details = document.getElementById('errorDetails');
    details.classList.toggle('hidden');
}

function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
    document.getElementById('importForm').reset();
    document.getElementById('importProgress').classList.add('hidden');
}

// Close modal on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImportModal();
    }
});

// Handle form submission with progress
document.getElementById('importForm').addEventListener('submit', function(e) {
    const progressDiv = document.getElementById('importProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    progressDiv.classList.remove('hidden');
    
    // Simulate progress
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        progressText.textContent = `Memproses... ${progress}%`;
        
        if (progress >= 90) {
            clearInterval(interval);
            progressText.textContent = 'Menyelesaikan import...';
        }
    }, 200);
});

// Auto hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = ['success-alert', 'error-alert', 'warning-alert', 'validation-alert'];
    alerts.forEach(alertId => {
        const alert = document.getElementById(alertId);
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease-out';
                setTimeout(() => alert.remove(), 500);
            }, 8000);
        }
    });
});
</script>
@endsection