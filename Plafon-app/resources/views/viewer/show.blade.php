@extends('layouts.app')

@section('title', 'Detail Pengajuan Selesai')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('viewer.index') }}" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Success Banner -->
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800">Pengajuan Telah Disetujui</p>
                <p class="text-xs text-green-700 mt-1">Pengajuan ini telah melalui semua tahap approval dan telah disetujui</p>
            </div>
        </div>
    </div>

    <!-- Main Info Card -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $submission->nama }}</h1>
                <p class="text-gray-600">Kode: {{ $submission->kode }}</p>
            </div>
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                ✓ Disetujui
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Sales</p>
                <div class="flex items-center mt-1">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-bold text-indigo-600">{{ substr($submission->sales->name, 0, 2) }}</span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $submission->sales->name }}</p>
                        <p class="text-xs text-gray-500">{{ $submission->sales->email }}</p>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Nama Kios</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->nama_kios }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Plafon</p>
                <p class="text-lg font-semibold text-green-600">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Jumlah Buka Faktur</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->jumlah_buka_faktur }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Alamat</p>
                <p class="text-gray-900">{{ $submission->alamat }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Tanggal Dibuat</p>
                <p class="text-gray-900">{{ $submission->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Tanggal Disetujui</p>
                <p class="text-gray-900">{{ $submission->updated_at->format('d M Y H:i') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Komitmen Pembayaran</p>
                <p class="text-gray-900">{{ $submission->komitmen_pembayaran }}</p>
            </div>
        </div>
    </div>

    <!-- Complete Approval History -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Approval Lengkap</h2>

        <div class="space-y-4">
            @foreach($submission->approvals->sortBy('level') as $approval)
            @php
                $isApproved = $approval->status === 'approved';
                $isRejected = $approval->status === 'rejected';
            @endphp

            <div class="flex items-start space-x-4 p-4 rounded-lg border
                {{ $isApproved ? 'bg-green-50 border-green-200' : '' }}
                {{ $isRejected ? 'bg-red-50 border-red-200' : '' }}
            ">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white
                        {{ $isApproved ? 'bg-green-500' : '' }}
                        {{ $isRejected ? 'bg-red-500' : '' }}
                    ">
                        {{ $approval->level }}
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <h3 class="font-semibold text-gray-900">{{ $approval->approver->name }}</h3>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">
                                Level {{ $approval->level }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $approval->created_at->format('d M Y H:i') }}
                        </span>
                    </div>

                    <!-- STATUS BADGE -->
                    <div class="flex items-center space-x-2 mb-2">
                        @if($isApproved)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                ✓ Disetujui
                            </span>
                        @elseif($isRejected)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                ✕ Ditolak
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                Menunggu
                            </span>
                        @endif
                    </div>

                    <!-- CATATAN -->
                    @if($approval->note)
                    <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1 font-semibold">
                            {{ $isRejected ? 'Alasan Penolakan:' : 'Catatan:' }}
                        </p>
                        <p class="text-sm text-gray-700 italic">
                            "{{ $approval->note }}"
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            @endforeach
        </div>
    </div>
</div>
@endsection