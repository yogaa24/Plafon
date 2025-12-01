@extends('layouts.app')

@section('title', 'Review Pengajuan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('approvals.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Main Info Card -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $submission->nama }}</h1>
                <p class="text-gray-600">Kode: {{ $submission->kode }}</p>
            </div>
            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                Level {{ $level }} Review
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-sm text-gray-500 mb-1">Nama Kios</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->nama_kios }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Plafon</p>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($submission->plafon, 0, ',', '.') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Alamat</p>
                <p class="text-gray-900">{{ $submission->alamat }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Jumlah Buka Faktur</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->jumlah_buka_faktur }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 mb-1">Sales</p>
                <p class="text-lg font-semibold text-gray-900">{{ $submission->sales->name }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">Komitmen Pembayaran</p>
                <p class="text-gray-900">{{ $submission->komitmen_pembayaran }}</p>
            </div>
        </div>

        <!-- Previous Approvals -->
        @if($submission->approvals->count() > 0)
        <div class="pt-6 border-t border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-3">Riwayat Approval Sebelumnya:</p>
            <div class="space-y-3">
                @foreach($submission->approvals as $approval)
                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold bg-green-500 text-white">
                        {{ $approval->level }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-gray-900">{{ $approval->approver->name }}</h3>
                            <span class="text-sm text-gray-500">{{ $approval->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            ✓ Disetujui
                        </span>
                        @if($approval->note)
                        <p class="text-sm text-gray-700 mt-2 italic">"{{ $approval->note }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Action Form -->
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Keputusan Approval</h2>

        <form action="{{ route('approvals.process', $submission) }}" method="POST" id="approvalForm">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-4">Pilih Keputusan:</label>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-500 transition has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="action" value="approved" required class="w-5 h-5 text-green-600 focus:ring-green-500">
                        <div class="ml-4">
                            <span class="font-semibold text-gray-900">Setujui</span>
                            <p class="text-sm text-gray-600">Pengajuan memenuhi persyaratan dan dapat dilanjutkan</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                        <input type="radio" name="action" value="revision" required class="w-5 h-5 text-orange-600 focus:ring-orange-500">
                        <div class="ml-4">
                            <span class="font-semibold text-gray-900">Minta Revisi</span>
                            <p class="text-sm text-gray-600">Pengajuan perlu diperbaiki oleh sales</p>
                        </div>
                    </label>

                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input type="radio" name="action" value="rejected" required class="w-5 h-5 text-red-600 focus:ring-red-500">
                        <div class="ml-4">
                            <span class="font-semibold text-gray-900">Tolak</span>
                            <p class="text-sm text-gray-600">Pengajuan tidak memenuhi persyaratan</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label for="note" class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan <span class="text-gray-500">(Wajib diisi jika tolak atau revisi)</span>
                </label>
                <textarea name="note" id="note" rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="Berikan catatan atau alasan keputusan Anda..."></textarea>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-indigo-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:scale-[1.02] transition shadow-lg">
                    Proses Pengajuan
                </button>
                <a href="{{ route('approvals.index') }}" class="flex-1 bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('approvalForm').addEventListener('submit', function(e) {
    const action = document.querySelector('input[name="action"]:checked');
    const note = document.getElementById('note').value.trim();
    
    if (!action) {
        e.preventDefault();
        alert('Silakan pilih keputusan terlebih dahulu!');
        return false;
    }
    
    if ((action.value === 'rejected' || action.value === 'revision') && !note) {
        e.preventDefault();
        alert('Catatan wajib diisi untuk keputusan tolak atau revisi!');
        return false;
    }
    
    const messages = {
        'approved': 'Apakah Anda yakin ingin menyetujui pengajuan ini?',
        'rejected': 'Apakah Anda yakin ingin menolak pengajuan ini?',
        'revision': 'Apakah Anda yakin ingin meminta revisi untuk pengajuan ini?'
    };
    
    if (!confirm(messages[action.value])) {
        e.preventDefault();
        return false;
    }
});
</script>
@endsection