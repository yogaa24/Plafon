@extends('layouts.app')

@section('title', 'Detail Pengajuan - ' . $submission->kode)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('direktur.index') }}" 
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard Pengajuan
            </a>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded">
                    {{ $submission->kode }}
                </span>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $submission->nama }} - {{ $submission->nama_kios }}
                </h1>
            </div>
            <p class="text-xs text-gray-500 mt-1">Diajukan pada {{ $submission->created_at->format('d F Y, H:i:s') }} oleh {{ optional($submission->sales)->name ?? 'Sales' }}</p>
        </div>

        <div class="flex items-center gap-2">
            {!! $submission->plafon_type_badge !!}
            {!! $submission->status_badge !!}
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 space-y-6">
        <!-- Informasi Umum & Plafon Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100">
            <!-- Customer & Kios -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 pb-1 border-b border-gray-100">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informasi Customer
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Nama Customer:</span>
                        <span class="font-semibold text-gray-900">{{ $submission->nama }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Nama Kios:</span>
                        <span class="font-semibold text-gray-900">{{ $submission->nama_kios }}</span>
                    </div>
                    @if(optional($submission->customer)->kode_customer)
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Kode Customer:</span>
                        <span class="font-semibold text-gray-900">{{ $submission->customer->kode_customer }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Sales:</span>
                        <span class="font-semibold text-indigo-700">{{ optional($submission->sales)->name ?? '-' }}</span>
                    </div>
                    <div class="py-1">
                        <span class="text-gray-500 block mb-1">Alamat:</span>
                        <span class="text-gray-900 bg-gray-50 p-2.5 rounded-lg block text-xs">{{ $submission->alamat ?: '-' }}</span>
                    </div>
                    @if($submission->keterangan)
                    <div class="py-1">
                        <span class="text-gray-500 block mb-1">Keterangan Sales:</span>
                        <span class="text-gray-900 bg-gray-50 p-2.5 rounded-lg block italic text-xs">"{{ $submission->keterangan }}"</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Nilai Plafon & Target -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 pb-1 border-b border-gray-100">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Finansial & Plafon
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Jenis:</span>
                        <span class="font-semibold text-gray-900">
                            {{ $submission->plafon_type === 'open' ? 'Open Plafon' : 'Rubah Plafon' }}
                            @if($submission->plafon_direction)
                                ({{ $submission->plafon_direction === 'naik' ? '↑ Naik' : '↓ Turun' }})
                            @endif
                        </span>
                    </div>
                    @if($submission->plafon_type === 'rubah' && $submission->plafon_sebelumnya)
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Plafon Sebelumnya:</span>
                        <span class="font-semibold text-gray-700">Rp {{ number_format($submission->plafon_sebelumnya, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Plafon Diajukan:</span>
                        <span class="font-bold text-emerald-700 text-base">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                    </div>
                    @if($submission->plafon_type === 'open' && $submission->jumlah_buka_faktur)
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Jumlah Buka Faktur:</span>
                        <span class="font-semibold text-gray-900">Rp {{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between py-1 border-b border-gray-50 items-center">
                        <span class="text-gray-500">Status Target:</span>
                        <span>{!! $submission->target_status_badge ?: '<span class="text-gray-400 text-xs">Belum Ditentukan</span>' !!}</span>
                    </div>
                </div>

                <!-- Komitmen Pembayaran -->
                <div class="pt-2">
                    @include('partials.komitmen-history', ['submission' => $submission])
                </div>
            </div>
        </div>

        <!-- Data OD & Over Collection -->
        @if($submission->payment_type && $submission->payment_data)
            @php
                $paymentData = is_array($submission->payment_data) ? $submission->payment_data : json_decode($submission->payment_data, true);
            @endphp
            <div class="p-4 bg-blue-50/70 rounded-xl border border-blue-200">
                <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-3">
                    Data Piutang & OD/Over Limit (Dari Team Collection)
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <span class="text-gray-500 block">Piutang:</span>
                        <span class="font-bold text-blue-900 text-sm">Rp {{ number_format($paymentData['piutang'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <span class="text-gray-500 block">Jml Over:</span>
                        <span class="font-bold text-rose-700 text-sm">Rp {{ number_format($paymentData['jml_over'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <span class="text-gray-500 block">OD 30 Hari:</span>
                        <span class="font-semibold text-gray-800 text-sm">Rp {{ number_format($paymentData['od_30'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <span class="text-gray-500 block">OD 60 Hari:</span>
                        <span class="font-semibold text-gray-800 text-sm">Rp {{ number_format($paymentData['od_60'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-blue-100">
                        <span class="text-gray-500 block">OD 90 Hari:</span>
                        <span class="font-semibold text-gray-800 text-sm">Rp {{ number_format($paymentData['od_90'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Lampiran Gambar -->
        @if($submission->lampiran_path)
            @php
                $lampiranPaths = is_array($submission->lampiran_path) ? $submission->lampiran_path : json_decode($submission->lampiran_path, true);
            @endphp
            @if($lampiranPaths && count($lampiranPaths) > 0)
                <div class="space-y-3 pb-6 border-b border-gray-100">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Lampiran Dokumen/Foto ({{ count($lampiranPaths) }})
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($lampiranPaths as $path)
                            <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden border border-gray-200 cursor-pointer shadow-xs hover:shadow-md transition group relative"
                                 onclick="openImageModal('{{ asset($path) }}')">
                                <img src="{{ asset($path) }}" alt="Lampiran" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold">
                                    Perbesar
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <!-- Riwayat Approval Lengkap / Audit Trail -->
        <div class="space-y-4">
            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Audit Trail & Riwayat Approval Lintas Level
            </h4>

            @if($submission->approvals && $submission->approvals->count() > 0)
                <div class="space-y-3">
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
                        <div class="p-3.5 rounded-lg border text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                            {{ $isAppr ? 'bg-green-50/60 border-green-200' : '' }}
                            {{ $isRev ? 'bg-amber-50/60 border-amber-200' : '' }}
                            {{ $isRej ? 'bg-rose-50/60 border-rose-200' : '' }}">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white shrink-0
                                    {{ $isAppr ? 'bg-green-600' : ($isRev ? 'bg-amber-500' : 'bg-rose-600') }}">
                                    {{ $approval->level }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 text-sm">{{ $levelRoleName }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-gray-600 font-medium">{{ optional($approval->approver)->name ?? 'User' }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        {{ $approval->created_at->format('d F Y, H:i:s') }}
                                    </div>
                                    @if($approval->note)
                                        <div class="mt-2 p-2.5 bg-white rounded-lg border border-gray-200/80 text-gray-800">
                                            <span class="font-semibold text-gray-600">Catatan:</span>
                                            <span>"{{ $approval->note }}"</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0 self-start sm:self-center">
                                <span class="px-3 py-1 rounded-full font-bold text-xs
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection
