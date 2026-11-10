<!-- Modal Kembalikan / Batalkan Approval -->
<div id="returnApprovalModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-[2px] overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 transition-opacity duration-200">
    <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-lg w-full overflow-hidden transform transition-all">
        <!-- Header -->
        <div id="returnModalHeader" class="px-6 py-4 bg-orange-600 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4M3 10l4-4m-4 4l4 4"/>
                </svg>
                <h3 id="returnModalTitle" class="text-base font-bold">Kembalikan ke Level Sebelumnya</h3>
            </div>
            <button type="button" onclick="closeReturnModal()" class="text-white/80 hover:text-white p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="returnApprovalForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-4">
                <!-- Info Alert -->
                <div id="returnModalAlert" class="p-3 bg-orange-50 border border-orange-200 rounded-xl text-xs text-orange-900 leading-relaxed">
                    <p class="font-semibold mb-1">Informasi:</p>
                    <p id="returnModalDesc">Pengajuan ini akan dikembalikan ke level sebelumnya. Status dan approval level tersebut akan di-reset agar dapat diproses ulang.</p>
                </div>

                <!-- Info Singkat Pengajuan -->
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Kode Pengajuan:</span>
                        <span id="returnModalKode" class="font-bold text-gray-900">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">Kios / Customer:</span>
                        <span id="returnModalCustomer" class="font-medium text-gray-800">-</span>
                    </div>
                </div>

                <!-- Input Catatan/Alasan -->
                <div>
                    <label for="returnModalNote" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Alasan Pengembalian / Pembatalan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="note" id="returnModalNote" rows="3" required
                              placeholder="Tuliskan alasan mengapa pengajuan ini dikembalikan/dibatalkan..."
                              class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    <p class="text-[11px] text-gray-500 mt-1">Alasan ini akan tersimpan dan dapat dibaca oleh approver level tujuan.</p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button type="button" onclick="closeReturnModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition">
                    Batal
                </button>
                <button type="submit" id="returnModalSubmitBtn" class="flex-1 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                    Ya, Kembalikan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(submissionId, kode, customer, targetLevelName) {
    const modal = document.getElementById('returnApprovalModal');
    const form = document.getElementById('returnApprovalForm');
    const title = document.getElementById('returnModalTitle');
    const desc = document.getElementById('returnModalDesc');
    const kodeEl = document.getElementById('returnModalKode');
    const custEl = document.getElementById('returnModalCustomer');
    const note = document.getElementById('returnModalNote');
    const submitBtn = document.getElementById('returnModalSubmitBtn');
    const header = document.getElementById('returnModalHeader');

    form.action = `/approvals/${submissionId}/return-to-previous`;
    title.textContent = `Kembalikan ke ${targetLevelName || 'Level Sebelumnya'}`;
    desc.textContent = `Pengajuan ini akan dikembalikan ke ${targetLevelName || 'level sebelumnya'}. Approval level tersebut akan di-reset agar dapat diperiksa/disetujui ulang.`;
    kodeEl.textContent = kode || '-';
    custEl.textContent = customer || '-';
    note.value = '';
    submitBtn.textContent = 'Ya, Kembalikan';
    submitBtn.className = 'flex-1 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg shadow-sm transition';
    header.className = 'px-6 py-4 bg-orange-600 text-white flex items-center justify-between';

    modal.classList.remove('hidden');
}

function openRecallModal(submissionId, kode, customer) {
    const modal = document.getElementById('returnApprovalModal');
    const form = document.getElementById('returnApprovalForm');
    const title = document.getElementById('returnModalTitle');
    const desc = document.getElementById('returnModalDesc');
    const kodeEl = document.getElementById('returnModalKode');
    const custEl = document.getElementById('returnModalCustomer');
    const note = document.getElementById('returnModalNote');
    const submitBtn = document.getElementById('returnModalSubmitBtn');
    const header = document.getElementById('returnModalHeader');

    form.action = `/approvals/${submissionId}/return-to-previous`;
    title.textContent = 'Batalkan Approval / Tarik Kembali';
    desc.textContent = 'Approval Anda pada pengajuan ini akan dibatalkan, dan pengajuan akan ditarik kembali ke dashboard antrean Anda untuk diproses ulang.';
    kodeEl.textContent = kode || '-';
    custEl.textContent = customer || '-';
    note.value = '';
    submitBtn.textContent = 'Ya, Batalkan Approval';
    submitBtn.className = 'flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-sm transition';
    header.className = 'px-6 py-4 bg-amber-600 text-white flex items-center justify-between';

    modal.classList.remove('hidden');
}

function closeReturnModal() {
    const modal = document.getElementById('returnApprovalModal');
    if (modal) modal.classList.add('hidden');
}

// Close on backdrop click
document.getElementById('returnApprovalModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});

// Dropdown Action Helper Functions
function toggleActionDropdown(event, menuId) {
    event.stopPropagation();
    const menu = document.getElementById(menuId);
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    closeAllActionDropdowns();

    if (isHidden) {
        if (!menu.dataset.inBody) {
            document.body.appendChild(menu);
            menu.dataset.inBody = "true";
        }

        menu.classList.remove('hidden');
        menu.style.position = 'fixed';
        menu.style.zIndex = '99999';

        const btn = event.currentTarget;
        const rect = btn.getBoundingClientRect();
        const menuWidth = menu.offsetWidth || 150;
        const menuHeight = menu.offsetHeight || 44;

        // Position horizontally: align right edge of menu with right edge of button
        let left = rect.right - menuWidth;
        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) {
            left = window.innerWidth - menuWidth - 10;
        }
        menu.style.left = left + 'px';
        menu.style.right = 'auto';

        // Position vertically: below button, or above if close to bottom
        const spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow < menuHeight + 10 && rect.top > menuHeight + 10) {
            menu.style.top = (rect.top - menuHeight - 6) + 'px';
        } else {
            menu.style.top = (rect.bottom + 6) + 'px';
        }
    }
}

function closeAllActionDropdowns() {
    document.querySelectorAll('.action-dropdown-menu').forEach(m => {
        m.classList.add('hidden');
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.action-dropdown-btn')) {
        closeAllActionDropdowns();
    }
});

window.addEventListener('scroll', closeAllActionDropdowns, true);
window.addEventListener('resize', closeAllActionDropdowns);
</script>

