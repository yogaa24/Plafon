@php
    $rawHistoryList = is_array($submission->komitmen_pembayaran_history) 
        ? $submission->komitmen_pembayaran_history 
        : (json_decode($submission->komitmen_pembayaran_history, true) ?: []);
    
    // Normalisasi riwayat: hanya gabungkan entri perbaikan duplikat terpisah dari TC versi lama
    $historyList = [];
    foreach ($rawHistoryList as $item) {
        $actionType = $item['action_type'] ?? '';
        $alasan = $item['alasan'] ?? '';
        $rejectedBy = $item['rejected_by_name'] ?? '';

        // Deteksi apakah ini entri revisi duplikat terpisah dari TC (versi lama yang tidak membawa penolakan Kadep)
        $isSeparateTcRevision = str_contains($alasan, 'diperbaiki oleh Collection pasca penolakan')
            && str_contains($rejectedBy, 'Collection')
            && empty($item['revised_at']);

        if ($isSeparateTcRevision && !empty($historyList)) {
            $lastIdx = count($historyList) - 1;
            // Jika entri Kadep sebelumnya belum punya komitmen baru atau berstatus returned_to_collection
            if (($historyList[$lastIdx]['action_type'] ?? '') === 'returned_to_collection' || empty($historyList[$lastIdx]['revised_at'])) {
                $historyList[$lastIdx]['komitmen_baru'] = $item['komitmen_baru'] ?? ($historyList[$lastIdx]['komitmen_baru'] ?? null);
                $historyList[$lastIdx]['revised_by_name'] = $rejectedBy;
                $historyList[$lastIdx]['revised_at'] = $item['created_at'] ?? now()->toDateTimeString();
                $historyList[$lastIdx]['action_type'] = 'revised_by_collection';
                continue;
            }
        }

        $historyList[] = $item;
    }

    $hasHistory = !empty($historyList) && count($historyList) > 0;
    $historyCount = $hasHistory ? count($historyList) : 0;
    $latestHistory = $hasHistory ? end($historyList) : null;
    $latestAction = $latestHistory['action_type'] ?? '';
    
    // Hanya dianggap 'returned_to_collection' jika aksi TERAKHIR masih penolakan oleh Kadep (belum diperbaiki TC)
    $isReturnedToCollection = $latestAction === 'returned_to_collection';
    if (!$isReturnedToCollection && empty($latestAction)) {
        $isReturnedToCollection = $submission->current_level == 2 && str_contains($submission->rejection_note ?? '', 'Kadep');
    }
    $isRevisedByCollection = $latestAction === 'revised_by_collection';
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600 font-medium">Komitmen Pembayaran:</span>
        @if($hasHistory)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ada Riwayat Penolakan ({{ $historyCount }}x)
            </span>
        @endif
    </div>

    <!-- Active Commitment Card -->
    <div class="p-2.5 rounded-lg border {{ $hasHistory ? 'bg-amber-50/40 border-amber-200' : 'bg-gray-50 border-gray-200' }}">
        <div class="flex items-start justify-between gap-2">
            <div class="text-sm font-semibold {{ $hasHistory ? 'text-amber-950' : 'text-gray-900' }} break-words">
                {{ $submission->komitmen_pembayaran }}
            </div>
        </div>
        @if($hasHistory)
            @if($isReturnedToCollection)
                <span class="inline-block mt-1 text-[11px] font-medium text-rose-700 bg-rose-100/80 px-2 py-0.5 rounded">
                    ⚠️ Ditolak oleh Kadep & Dikembalikan ke Collection untuk direvisi
                </span>
            @elseif($isRevisedByCollection)
                <span class="inline-block mt-1 text-[11px] font-medium text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded">
                    ✓ Komitmen Telah Diperbaiki oleh Collection
                </span>
            @else
                <span class="inline-block mt-1 text-[11px] font-medium text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded">
                    Komitmen Aktif (Telah disesuaikan oleh Approver)
                </span>
            @endif
        @endif
    </div>

    <!-- History Expandable Section -->
    @if($hasHistory)
        <details class="group bg-white border border-gray-200 rounded-lg overflow-hidden text-xs transition">
            <summary class="px-3 py-2 cursor-pointer bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold flex items-center justify-between select-none">
                <span class="flex items-center gap-1.5 text-gray-700">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Lihat Riwayat Penolakan Komitmen ({{ $historyCount }})
                </span>
                <svg class="w-3.5 h-3.5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>
            <div class="p-3 space-y-2.5 bg-gray-50/50 border-t border-gray-100">
                @foreach($historyList as $index => $history)
                    @php
                        $isReturned = ($history['action_type'] ?? '') === 'returned_to_collection';
                        $isRevised = ($history['action_type'] ?? '') === 'revised_by_collection' || !empty($history['revised_at']) || !empty($history['revised_by_name']);
                    @endphp
                    <div class="p-2.5 bg-white rounded-md border border-gray-200 shadow-2xs space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] text-gray-500 border-b border-gray-100 pb-1">
                            <span class="font-bold text-amber-700">Penolakan #{{ $loop->iteration }}</span>
                            <span>{{ !empty($history['created_at']) ? \Carbon\Carbon::parse($history['created_at'])->format('d M Y H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-red-600 font-medium block text-[11px]">Komitmen yang Ditolak:</span>
                            <span class="line-through text-gray-600 italic bg-red-50/60 px-1.5 py-0.5 rounded block mt-0.5 border border-red-100">
                                "{{ $history['komitmen_sebelumnya'] ?? '-' }}"
                            </span>
                        </div>
                        <div>
                            <span class="text-amber-800 font-medium block text-[11px]">Alasan Penolakan:</span>
                            <span class="text-gray-800 block mt-0.5 bg-amber-50/60 px-1.5 py-0.5 rounded border border-amber-100">
                                {{ $history['alasan'] ?? '-' }}
                            </span>
                        </div>
                        @if(!empty($history['rejected_by_name']))
                            <div class="text-[10px] text-gray-500">
                                Ditolak oleh: <span class="font-semibold text-gray-700">{{ $history['rejected_by_name'] }}</span>
                            </div>
                        @endif

                        {{-- Komitmen Hasil Perbaikan TC --}}
                        @if(!empty($history['komitmen_baru']) && $history['komitmen_baru'] !== '(Menunggu revisi komitmen dari Collection)')
                            <div class="pt-1.5 border-t border-gray-100">
                                <span class="text-blue-700 font-medium block text-[11px]">
                                    {{ $isRevised ? 'Komitmen Baru Hasil Perbaikan Collection:' : 'Komitmen Baru yang Ditetapkan:' }}
                                </span>
                                <span class="text-blue-950 font-semibold block mt-0.5 bg-blue-50/60 px-1.5 py-0.5 rounded border border-blue-100">
                                    "{{ $history['komitmen_baru'] }}"
                                </span>
                                @if(!empty($history['revised_by_name']))
                                    <div class="text-[10px] text-blue-600 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Diperbaiki oleh: <span class="font-medium text-blue-800">{{ $history['revised_by_name'] }}</span>
                                        @if(!empty($history['revised_at']))
                                            <span class="text-gray-400">({{ \Carbon\Carbon::parse($history['revised_at'])->format('d M Y H:i') }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @elseif($isReturned)
                            <div class="pt-1.5 border-t border-gray-100 text-[11px] text-rose-600 font-medium italic flex items-center gap-1">
                                <span>⏳ Menunggu perbaikan komitmen oleh Collection</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </details>
    @endif
</div>
