@extends('layouts.app')

@section('title', 'Riwayat Approval')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Approval Level {{ $level }}</h1>
            <p class="text-sm text-gray-600">Semua pengajuan yang telah Anda proses</p>
        </div>
        <a href="{{ route('approvals.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            ← Kembali ke Dashboard
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('approvals.history') }}" id="filterForm" class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Cari kode, nama, kios, atau sales..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Status Filter -->
                <div class="w-44">
                    <select name="status_filter" 
                        onchange="document.getElementById('filterForm').submit()" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="approved" {{ request('status_filter') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status_filter') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <!-- Sales Filter -->
                <div class="w-52">
                    <select name="sales_id" 
                        onchange="document.getElementById('filterForm').submit()" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 cursor-pointer">
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Date To -->
                <div class="w-44">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Buttons -->
                <div class="flex gap-2 ml-auto">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                        Filter
                    </button>
                    <a href="{{ route('approvals.history') }}" class="px-5 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status Pengajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tanggal Proses</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Detail</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($approvals as $index => $approval)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center space-y-1">
                                <span class="text-sm font-semibold text-gray-900">{{ $approvals->firstItem() + $index }}</span>
                                <button type="button" onclick="toggleDetail({{ $approval->id }})" class="text-gray-500 hover:text-indigo-600 focus:outline-none transition" title="Lihat Detail">
                                    <svg id="icon-{{ $approval->id }}" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $approval->submission->nama }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-900">{{ $approval->submission->nama_kios }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $approval->submission->sales->name }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm font-semibold text-gray-900">
                                Rp {{ number_format($approval->submission->plafon, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {!! $approval->submission->plafon_type_badge !!}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($approval->status === 'approved')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ Disetujui
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    ✖ Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @php
                                $submissionStatus = $approval->submission->status;
                            @endphp
                            @if($submissionStatus === 'done')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Selesai
                                </span>
                            @elseif($submissionStatus === 'rejected')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Ditolak
                                </span>
                            @elseif($submissionStatus === 'pending_viewer')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Di Viewer
                                </span>
                            @elseif(str_starts_with($submissionStatus, 'approved_'))
                                @php
                                    $currentLevel = intval(str_replace('approved_', '', $submissionStatus)) + 1;
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Level {{ $currentLevel }}
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($submissionStatus) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="text-sm text-gray-600">{{ $approval->created_at->format('d M Y H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleDetail({{ $approval->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                Lihat
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Detail Row -->
                    <tr id="detail-{{ $approval->id }}" class="hidden bg-gray-50">
                        <td colspan="10" class="px-4 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-white rounded-lg border border-gray-200">
                                <!-- Informasi Pengajuan -->
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Informasi Pengajuan</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Kode:</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $approval->submission->kode }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Alamat:</span>
                                            <span class="text-sm text-gray-900">{{ $approval->submission->alamat }}</span>
                                        </div>
                                        @if($approval->submission->plafon_type === 'open')
                                        <div class="flex justify-between py-1 border-b border-gray-100">
                                            <span class="text-sm text-gray-600">Jumlah Value Faktur:</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                Rp {{ number_format($approval->submission->jumlah_buka_faktur, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @endif
                                        <div class="py-1">
                                            <span class="text-sm text-gray-600 block mb-1">Komitmen Pembayaran:</span>
                                            <span class="text-sm text-gray-900">{{ $approval->submission->komitmen_pembayaran }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Keputusan Anda -->
                                <div>
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Keputusan Anda</h4>
                                    <div class="space-y-3">
                                        <div class="p-3 rounded-lg {{ $approval->status === 'approved' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-sm font-semibold {{ $approval->status === 'approved' ? 'text-green-700' : 'text-red-700' }}">
                                                    {{ $approval->status === 'approved' ? '✓ DISETUJUI' : '✖ DITOLAK' }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ $approval->created_at->format('d M Y H:i') }}
                                                </span>
                                            </div>
                                            @if($approval->note)
                                            <div class="mt-2 p-2 bg-white rounded border border-gray-200">
                                                <p class="text-xs text-gray-600 mb-1">Catatan:</p>
                                                <p class="text-sm text-gray-900 italic">"{{ $approval->note }}"</p>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Data Payment jika Level 2 Open Plafon -->
                                        @if($level == 2 && $approval->submission->plafon_type === 'open' && $approval->status === 'approved')
                                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-xs font-semibold text-blue-900 mb-2">Data Verifikasi:</p>
                                            <div class="space-y-1 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700">Piutang:</span>
                                                    <span class="font-semibold">Rp {{ number_format($approval->piutang ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700">Jml Over:</span>
                                                    <span class="font-semibold">Rp {{ number_format($approval->jml_over ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700">Jml OD 30:</span>
                                                    <span class="font-semibold">Rp {{ number_format($approval->jml_od_30 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700">Jml OD 60:</span>
                                                    <span class="font-semibold">Rp {{ number_format($approval->jml_od_60 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-blue-700">Jml OD 90:</span>
                                                    <span class="font-semibold">Rp {{ number_format($approval->jml_od_90 ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Riwayat Approval Lengkap -->
                                @if($approval->submission->approvals->count() > 0)
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Riwayat Approval Lengkap</h4>
                                    <div class="space-y-2">
                                        @php
                                            $previousTime = $approval->submission->created_at;
                                        @endphp
                                        @foreach($approval->submission->approvals->sortBy('level') as $appr)
                                        @php
                                            // Hitung durasi dari waktu sebelumnya ke waktu approval ini
                                            $seconds = $previousTime->diffInSeconds($appr->created_at);

                                            // Format durasi
                                            if ($seconds < 60) {
                                                $durationText = $seconds . ' detik';
                                                $durationClass = 'bg-green-100 text-green-700';
                                            } elseif ($seconds < 3600) {
                                                $minutes = ceil($seconds / 60);
                                                $durationText = $minutes . ' menit';
                                                $durationClass = $minutes < 30 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                                            } else {
                                                $hours = floor($seconds / 3600);
                                                $minutes = ceil(($seconds % 3600) / 60);
                                                $durationText = $hours . ' jam ' . $minutes . ' menit';
                                                $durationClass = $hours < 2 ? 'bg-yellow-100 text-yellow-700' : 'bg-orange-100 text-orange-700';
                                            }

                                            // Update waktu sebelumnya untuk iterasi berikutnya
                                            $previousTime = $appr->created_at;
                                        @endphp
                                        
                                        <div class="flex items-start justify-between p-3 rounded-lg border 
                                            {{ $appr->status === 'approved' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                            <div class="flex items-center space-x-3 flex-1">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm 
                                                    {{ $appr->status === 'approved' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                                    {{ $appr->level }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <p class="font-semibold text-gray-900 text-sm">{{ $appr->approver->name }}</p>
                                                        <!-- Badge Durasi -->
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $durationClass }}">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            {{ $durationText }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-gray-500">{{ $appr->created_at->format('d M Y H:i:s') }}</p>
                                                    @if($appr->note)
                                                        <p class="text-xs text-gray-700 mt-1 italic">"{{ $appr->note }}"</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full font-semibold whitespace-nowrap
                                                {{ $appr->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $appr->status === 'approved' ? '✓ Disetujui' : '✖ Ditolak' }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Total Waktu Proses -->
                                    @php
                                        $lastApproval = $approval->submission->approvals->sortBy('level')->last();
                                        $totalSeconds = $approval->submission->created_at->diffInSeconds($lastApproval->created_at);

                                        if ($totalSeconds < 60) {
                                            $totalDurationText = $totalSeconds . ' detik';
                                        } elseif ($totalSeconds < 3600) {
                                            $totalDurationText = ceil($totalSeconds / 60) . ' menit';
                                        } else {
                                            $totalHours = floor($totalSeconds / 3600);
                                            $totalMinutes = ceil(($totalSeconds % 3600) / 60);
                                            $totalDurationText = $totalHours . ' jam ' . $totalMinutes . ' menit';
                                        }
                                    @endphp
                                    
                                    <div class="mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-indigo-900">Total Waktu Proses:</span>
                                            <span class="text-sm font-bold text-indigo-700">{{ $totalDurationText }}</span>
                                        </div>
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
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ request('search') ? 'Tidak ada hasil yang cocok dengan filter' : 'Anda belum memproses pengajuan apapun' }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($approvals->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $approvals->links() }}
        </div>
        @endif
    </div>

    <!-- Summary Info -->
    <div class="text-sm text-gray-600">
        Menampilkan {{ $approvals->firstItem() ?? 0 }} - {{ $approvals->lastItem() ?? 0 }} dari {{ $approvals->total() }} riwayat
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
</script>
@endsection