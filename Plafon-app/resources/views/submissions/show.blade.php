@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('submissions.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Info Jenis Pengajuan -->
    @if($submission->plafon_type === 'rubah')
    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg mb-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-purple-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold text-purple-900 mb-2">Pengajuan Rubah Plafon</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @if($submission->previousSubmission)
                    <div>
                        <span class="text-purple-700 font-medium">Plafon Sebelumnya:</span>
                        <span class="text-purple-900 ml-2 font-semibold">Rp {{ number_format($submission->previousSubmission->plafon, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-purple-700 font-medium">Plafon Usulan:</span>
                        <span class="text-purple-900 ml-2 font-bold">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-purple-700 font-medium">Status Perubahan:</span>
                        <span class="ml-2">
                            @if($submission->plafon_direction === 'naik')
                                @php
                                    $selisih = $submission->plafon - $submission->previousSubmission->plafon;
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    Naik Rp {{ number_format($selisih, 0, ',', '.') }}
                                </span>
                            @elseif($submission->plafon_direction === 'turun')
                                @php
                                    $selisih = $submission->previousSubmission->plafon - $submission->plafon;
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                    Turun Rp {{ number_format($selisih, 0, ',', '.') }}
                                </span>
                            @endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Info Card -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $submission->nama }}</h1>
                    {!! $submission->plafon_type_badge !!}
                    @if($submission->plafon_type === 'rubah' && $submission->plafon_direction)
                        {!! $submission->plafon_direction_badge !!}
                    @endif
                </div>
                <p class="text-gray-600">Kode: {{ $submission->kode }}</p>
            </div>
            {!! $submission->status_badge !!}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Nama Kios</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->nama_kios }}</p>
            </div>
            
            @if($submission->plafon_type === 'rubah' && $submission->previousSubmission)
            <!-- Tampilan Khusus Rubah Plafon -->
            <div>
                <p class="text-sm text-gray-500 mb-1">Plafon Sebelumnya</p>
                <p class="text-lg font-semibold text-gray-500 line-through">Rp {{ number_format($submission->previousSubmission->plafon, 0, ',', '.') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Plafon yang Diusulkan</p>
                <div class="flex items-center gap-3">
                    <p class="text-2xl font-bold text-indigo-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</p>
                    @if($submission->plafon_direction === 'naik')
                        @php
                            $selisih = $submission->plafon - $submission->previousSubmission->plafon;
                        @endphp
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                            +Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @elseif($submission->plafon_direction === 'turun')
                        @php
                            $selisih = $submission->previousSubmission->plafon - $submission->plafon;
                        @endphp
                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-lg text-sm font-semibold">
                            -Rp {{ number_format($selisih, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>
            @else
            <!-- Tampilan Normal Open Plafon -->
            <div>
                <p class="text-sm text-gray-500 mb-1">Plafon</p>
                <p class="text-lg font-bold text-indigo-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</p>
            </div>
            @endif
            
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Alamat</p>
                <p class="text-gray-900">{{ $submission->alamat }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Jumlah Buka Faktur</p>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($submission->jumlah_buka_faktur, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Tanggal Dibuat</p>
                <p class="text-gray-900">{{ $submission->created_at->format('d M Y H:i') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Komitmen Pembayaran</p>
                <p class="text-gray-900">{{ $submission->komitmen_pembayaran }}</p>
            </div>
        </div>

        @if($submission->revision_note)
        <div class="mt-6 p-4 bg-orange-50 border-l-4 border-orange-500 rounded-lg">
            <p class="text-sm font-semibold text-orange-800 mb-2">Catatan Revisi:</p>
            <p class="text-sm text-orange-700">{{ $submission->revision_note }}</p>
        </div>
        @endif

        @if($submission->rejection_note)
        <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
            <p class="text-sm font-semibold text-red-800 mb-2">Alasan Penolakan:</p>
            <p class="text-sm text-red-700">{{ $submission->rejection_note }}</p>
        </div>
        @endif
    </div>

    <!-- Approval History -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Approval</h2>

        @if($submission->approvals->count() > 0)

            @php
                // Urutkan approval dari yang terbaru (created_at DESC)
                $sortedApprovals = $submission->approvals->sortByDesc('created_at');

                // Jika show=all → tampilkan semua
                // Jika tidak → tampilkan hanya 2 approval terbaru
                $approvalsToShow = $showAll 
                    ? $sortedApprovals
                    : $sortedApprovals->take(2);
            @endphp

            <div class="space-y-4">
                @foreach($approvalsToShow as $approval)
                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold
                            @if($approval->status == 'approved') bg-green-500 text-white
                            @elseif($approval->status == 'rejected') bg-red-500 text-white
                            @else bg-orange-500 text-white
                            @endif">
                            {{ $approval->level }}
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">{{ $approval->approver->name }}</h3>
                            <span class="text-sm text-gray-500">
                                {{ $approval->created_at->format('d M Y H:i') }}
                            </span>
                        </div>

                        <p class="text-sm mb-2">
                            @if($approval->status == 'approved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                ✓ Disetujui
                            </span>
                            @elseif($approval->status == 'rejected')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                ✗ Ditolak
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                ↻ Perlu Revisi
                            </span>
                            @endif
                        </p>

                        @if($approval->note)
                        <p class="text-sm text-gray-700 mt-2 italic">"{{ $approval->note }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Tombol Lihat Semua --}}
            @if(!$showAll && $submission->approvals->count() > 2)
                <div class="mt-4">
                    <a href="?show=all" 
                    class="text-blue-600 hover:underline font-semibold">
                        Lihat Semua ({{ $submission->approvals->count() }} approval)
                    </a>
                </div>
            @endif

        @else
            <p class="text-center text-gray-500 py-8">Belum ada riwayat approval</p>
        @endif
    </div>

</div>
@endsection