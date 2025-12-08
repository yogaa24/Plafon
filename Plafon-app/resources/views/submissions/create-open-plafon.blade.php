@extends('layouts.app')

@section('title', 'Open Plafon Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Open Plafon Sementara</h1>
            <p class="text-sm text-gray-600 mt-1">Buat pengajuan open plafon sementara customer</p>
        </div>
        <a href="{{ route('submissions.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
            ← Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('submissions.store') }}" method="POST" id="openPlafonForm" enctype="multipart/form-data">
            @csrf
            
            <!-- Hidden fields -->
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <input type="hidden" name="plafon_type" value="open">
            <input type="hidden" name="plafon" value="{{ $customer->plafon_aktif }}">

            <!-- Kode (Read Only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Pengajuan <span class="text-red-500">*</span>
                </label>
                <input type="text" value="{{ $kode }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono">
                <p class="text-xs text-gray-500 mt-1">Kode akan digenerate otomatis saat pengajuan disimpan</p>
            </div>

            <!-- Read-Only Customer Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Customer</label>
                    <input type="text" value="{{ $customer->nama }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kios</label>
                    <input type="text" value="{{ $customer->nama_kios }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                <textarea readonly rows="2" class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">{{ $customer->alamat }}</textarea>
            </div>

            <!-- Editable Fields -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Open Plafon Baru</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Plafon Saat Ini (Read Only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plafon saat ini
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="text" 
                                value="{{ number_format($customer->plafon_aktif, 0, ',', '.') }}" 
                                readonly 
                                class="w-full pl-12 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-medium">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Plafon yang sedang aktif</p>
                    </div>

                    <!-- Jumlah Buka Faktur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Value Faktur <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="jumlah_buka_faktur" 
                            value="{{ old('jumlah_buka_faktur') }}"
                            required 
                            min="1"
                            class="w-full px-4 py-2.5 border @error('jumlah_buka_faktur') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan jumlah buka faktur">
                        @error('jumlah_buka_faktur')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Jumlah faktur yang dapat dibuka</p>
                    </div>
                </div>

                <!-- Jenis Pembayaran Section (Optional) -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Jenis Pembayaran <span class="text-red-500">*</span></h4>
                    
                    <!-- Radio Buttons: OD or Over -->
                    <div class="flex gap-8 mb-4">
                        <div class="flex items-center">
                            <input 
                                type="radio" 
                                id="type_od" 
                                name="payment_type" 
                                value="od"
                                required
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                onchange="togglePaymentType()">
                            <label for="type_od" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                OD
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input 
                                type="radio" 
                                id="type_over" 
                                name="payment_type" 
                                value="over"
                                required
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                onchange="togglePaymentType()">
                            <label for="type_over" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                Over
                            </label>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <!-- OD Section -->
                        <div id="odSection" class="space-y-3 hidden">
                            <!-- Piutang -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Piutang</label>
                                <input 
                                    type="number" 
                                    id="od_piutang_value"
                                    name="od_piutang_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml Over -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml Over</label>
                                <input 
                                    type="number" 
                                    id="od_jml_over_value"
                                    name="od_jml_over_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 30 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 30</label>
                                <input 
                                    type="number" 
                                    id="od_30_value"
                                    name="od_30_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 60 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 60</label>
                                <input 
                                    type="number" 
                                    id="od_60_value"
                                    name="od_60_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 90 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 90</label>
                                <input 
                                    type="number" 
                                    id="od_90_value"
                                    name="od_90_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>
                        </div>


                        <!-- Over Section -->
                        <div id="overSection" class="space-y-3 hidden">
                            <!-- Piutang -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Piutang</label>
                                <input 
                                    type="number" 
                                    id="over_piutang_value"
                                    name="over_piutang_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml Over -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml Over</label>
                                <input 
                                    type="number" 
                                    id="over_jml_over_value"
                                    name="over_jml_over_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 30 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 30</label>
                                <input 
                                    type="number" 
                                    id="over_od_30_value"
                                    name="over_od_30_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 60 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 60</label>
                                <input 
                                    type="number" 
                                    id="over_od_60_value"
                                    name="over_od_60_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>

                            <!-- Jml OD 90 -->
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Jml OD 90</label>
                                <input 
                                    type="number" 
                                    id="over_od_90_value"
                                    name="over_od_90_value"
                                    min="0"
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg 
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jumlah">
                            </div>
                        </div>


                        <p class="text-xs text-gray-500 mt-3">* Jika diisi, pilih OD atau Over kemudian centang item yang diinginkan</p>
                    </div>
                </div>

                <!-- Komitmen Pembayaran -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="komitmen_pembayaran" 
                        rows="3" 
                        required
                        class="w-full px-4 py-2.5 border @error('komitmen_pembayaran') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: Pembayaran setiap hari Senin dan Kamis">{{ old('komitmen_pembayaran') }}</textarea>
                    @error('komitmen_pembayaran')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan (Opsional)
                    </label>
                    <textarea 
                        name="keterangan" 
                        rows="3" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan') }}</textarea>
                </div>

                <!-- Upload Lampiran -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Lampiran Gambar (Opsional)
                    </label>
                    <input 
                        type="file" 
                        name="lampiran" 
                        accept="image/*"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="previewImage(event)">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, JPEG (Max: 2MB)</p>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3 hidden">
                        <img id="preview" class="max-w-xs rounded-lg border border-gray-300" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('submissions.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ajukan Open Plafon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePaymentType() {
    const od = document.getElementById('type_od').checked;
    const over = document.getElementById('type_over').checked;

    document.getElementById('odSection').classList.toggle('hidden', !od);
    document.getElementById('overSection').classList.toggle('hidden', !over);
}


function toggleOdInput(checkbox, inputId) {
    const input = document.getElementById(inputId);
    input.disabled = !checkbox.checked;
    if (!checkbox.checked) {
        input.value = '';
    }
}

function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB');
            event.target.value = '';
            previewContainer.classList.add('hidden');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.classList.add('hidden');
    }
}

// Validate before submit
document.getElementById('openPlafonForm').addEventListener('submit', function(e) {
    const jumlahBukaFaktur = parseInt(document.querySelector('[name="jumlah_buka_faktur"]').value) || 0;
    
    // Validate jumlah buka faktur
    if (jumlahBukaFaktur <= 0) {
        e.preventDefault();
        alert('Jumlah buka faktur harus lebih besar dari 0');
        return;
    }
    
    // Check if payment type is selected
    const typeOd = document.getElementById('type_od');
    const typeOver = document.getElementById('type_over');
    
    // If payment type is selected, validate the items
    if (typeOd.checked || typeOver.checked) {
        let hasInvalidValue = false;
        
        if (typeOver.checked && !hasInvalidValue) {
            const overCheckboxes = document.querySelectorAll('[name="over_items[]"]:checked');
            
            if (overCheckboxes.length > 0) {
                // Validate each selected item has a valid value
                overCheckboxes.forEach(cb => {
                    const valueInputId = cb.id + '_value';
                    const valueInput = document.getElementById(valueInputId);
                    const value = parseInt(valueInput.value) || 0;
                    
                    if (value <= 0) {
                        hasInvalidValue = true;
                        e.preventDefault();
                        alert(`Masukkan jumlah yang valid untuk ${cb.nextElementSibling.textContent}`);
                    }
                });
            }
        }
        
        if (hasInvalidValue) {
            return;
        }
    }
    
    // Confirmation
    const plafonValue = {{ $customer->plafon_aktif }};
    const formattedPlafon = new Intl.NumberFormat('id-ID').format(plafonValue);
    
    let paymentType = 'Tidak ada';
    if (typeOd.checked) paymentType = 'OD';
    if (typeOver.checked) paymentType = 'Over';
    
    const confirmMessage = `Konfirmasi Pengajuan Open Plafon:\n\n` +
        `Plafon: Rp ${formattedPlafon}\n` +
        `Jumlah Buka Faktur: ${jumlahBukaFaktur}\n` +
        `Jenis Pembayaran: ${paymentType}\n\n` +
        `Lanjutkan pengajuan?`;
    
    const confirmed = confirm(confirmMessage);
    
    if (!confirmed) {
        e.preventDefault();
    }
});
</script>
@endsection