@extends('layouts.app')

@section('title', 'Dashboard Piutang Manager')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Piutang Manager</h1>
            <p class="text-sm text-gray-600 mt-1">
                Kelola data piutang semua customer
            </p>
        </div>

        <!-- Import Button -->
        <a href="{{ route('piutang-manager.import-piutang') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Import Piutang
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
        <form method="GET" action="{{ route('piutang-manager.index') }}" id="filterForm" class="space-y-3">
            <div class="flex flex-wrap gap-3">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Cari kode, nama, atau kios customer..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Sales Filter -->
                <div class="w-52">
                    <select name="sales_id" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Sales</option>
                        @foreach($salesList as $sales)
                        <option value="{{ $sales->id }}" {{ request('sales_id') == $sales->id ? 'selected' : '' }}>
                            {{ $sales->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-44">
                    <select name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                        Filter
                    </button>
                    <a href="{{ route('piutang-manager.index') }}" 
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Kios</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sales</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Plafon Aktif</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Piutang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $index => $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center text-sm text-gray-900">{{ $customers->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $customer->kode_customer }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $customer->nama_kios }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $customer->sales->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-700">
                            Rp {{ number_format($customer->plafon_aktif, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold {{ $customer->piutang > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            @if($customer->piutang > 0)
                                Rp {{ number_format($customer->piutang, 0, ',', '.') }}
                            @else
                                <span class="italic">Rp 0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($customer->status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('piutang-manager.show', $customer) }}" 
                               class="text-blue-600 hover:text-blue-900 font-medium text-sm" 
                               title="Lihat Detail">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data customer</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ request('search') ? 'Tidak ada hasil yang cocok dengan pencarian' : 'Belum ada customer terdaftar' }}
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($customers->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- Summary -->
    <div class="text-sm text-gray-600">
        Menampilkan {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} dari {{ $customers->total() }} customer
    </div>
</div>

@if(session('success'))
<div id="success-alert" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
</div>
@endif

<script>
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