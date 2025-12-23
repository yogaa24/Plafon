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
                            Plafon Aktif
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
                            placeholder="Masukkan jumlah value faktur">
                         @error('jumlah_buka_faktur')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                         @enderror
                    </div>
                </div>

                <!-- Jenis Pembayaran Section -->
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
                                <label class="text-sm text-gray-700 flex items-center">
                                    Jml Over
                                    <svg class="w-4 h-4 ml-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Auto-filled dari Plafon - (Value Faktur + Piutang)">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </label>
                                <input 
                                    type="number" 
                                    id="over_jml_over_value"
                                    name="over_jml_over_value"
                                    readonly
                                    class="w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-blue-50
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Auto-filled">
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

                        <p class="text-xs text-gray-500 mt-3">* Pilih OD atau Over kemudian isi item yang diinginkan</p>
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
            </div>

            <!-- TAMBAHKAN INI - Upload Lampiran -->
            <div class="mt-6">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Lampiran <span class="text-gray-400">(Opsional, Maksimal 3 gambar)</span>
                    </label>
                    <input type="file" 
                        name="lampiran[]" 
                        id="lampiranInput" 
                        accept="image/jpeg,image/jpg,image/png" 
                        multiple
                        max="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="previewMultipleLampiran(event)">
                    <p class="text-xs text-gray-500 mt-1">
                        Format: JPG, JPEG, PNG. Gambar akan otomatis dikompres menjadi ±500KB
                    </p>
                    
                    <!-- Preview Multiple Images -->
                    <div id="lampiranPreviewContainer" class="mt-3 hidden">
                        <div id="lampiranPreviewList" class="grid grid-cols-3 gap-2"></div>
                        <p class="text-xs text-gray-500 mt-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span id="imageCountText">0 gambar dipilih</span> - Gambar akan dikompres otomatis saat diupload
                        </p>
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
// Data plafon aktif dari backend
const plafonAktif = {{ $customer->plafon_aktif }};

// Toggle payment type sections
function togglePaymentType() {
    const od = document.getElementById('type_od').checked;
    const over = document.getElementById('type_over').checked;

    document.getElementById('odSection').classList.toggle('hidden', !od);
    document.getElementById('overSection').classList.toggle('hidden', !over);
    
    // Auto-calculate when switching type
    if (over) {
        calculateOverAutoFill();
    }
}

// Calculate auto-fill for Over payment type
function calculateOverAutoFill() {
    if (!document.getElementById('type_over').checked) return;
    
    const valueFaktur = parseFloat(document.querySelector('[name="jumlah_buka_faktur"]').value) || 0;
    const piutang = parseFloat(document.getElementById('over_piutang_value').value) || 0;
    
    // Rumus: Plafon Aktif - (Value Faktur + Piutang)
    const jmlOver = plafonAktif - (valueFaktur + piutang);
    
    // Update field (boleh minus)
    const overJmlOverField = document.getElementById('over_jml_over_value');
    if (overJmlOverField) {
        overJmlOverField.value = Math.round(jmlOver);
    }
}

// Trigger calculation when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const jumlahBukaFakturInput = document.querySelector('[name="jumlah_buka_faktur"]');
    const overPiutangInput = document.getElementById('over_piutang_value');
    
    // Calculate when Value Faktur changes
    if (jumlahBukaFakturInput) {
        jumlahBukaFakturInput.addEventListener('input', calculateOverAutoFill);
        jumlahBukaFakturInput.addEventListener('change', calculateOverAutoFill);
    }
    
    // Calculate when Piutang (Over) changes
    if (overPiutangInput) {
        overPiutangInput.addEventListener('input', calculateOverAutoFill);
        overPiutangInput.addEventListener('change', calculateOverAutoFill);
    }
});

// Validate before submit (validasi minimal)
document.getElementById('openPlafonForm').addEventListener('submit', function(e) {
    const jumlahBukaFaktur = parseInt(document.querySelector('[name="jumlah_buka_faktur"]').value) || 0;
    
    // Validate jumlah buka faktur harus lebih dari 0
    if (jumlahBukaFaktur <= 0) {
        e.preventDefault();
        alert('Jumlah value faktur harus lebih besar dari 0');
        return;
    }
    
    // Check if payment type is selected
    const typeOd = document.getElementById('type_od');
    const typeOver = document.getElementById('type_over');
    
    // Validate payment type is selected
    if (!typeOd.checked && !typeOver.checked) {
        e.preventDefault();
        alert('Pilih jenis pembayaran: OD atau Over');
        return;
    }
    
    // Confirmation
    let paymentType = 'Tidak ada';
    if (typeOd.checked) paymentType = 'OD';
    if (typeOver.checked) paymentType = 'Over';
    
    const formattedPlafon = new Intl.NumberFormat('id-ID').format(plafonAktif);
    const formattedValueFaktur = new Intl.NumberFormat('id-ID').format(jumlahBukaFaktur);
    
    let confirmMessage = `Konfirmasi Pengajuan Open Plafon:\n\n` +
        `Plafon Aktif: Rp ${formattedPlafon}\n` +
        `Jumlah Value Faktur: Rp ${formattedValueFaktur}\n` +
        `Jenis Pembayaran: ${paymentType}\n`;
    
    // Tambahkan info Jml Over jika Over dipilih
    if (typeOver.checked) {
        const jmlOver = parseInt(document.getElementById('over_jml_over_value').value) || 0;
        const formattedJmlOver = new Intl.NumberFormat('id-ID').format(jmlOver);
        confirmMessage += `Jml Over: Rp ${formattedJmlOver}\n`;
        
        if (jmlOver < 0) {
            confirmMessage += `\n⚠️ Perhatian: Jml Over bernilai negatif!\n`;
        }
    }
    
    confirmMessage += `\nLanjutkan pengajuan?`;
    
    const confirmed = confirm(confirmMessage);
    
    if (!confirmed) {
        e.preventDefault();
    }
});
function previewMultipleLampiran(event) {
    const files = Array.from(event.target.files);
    const previewContainer = document.getElementById('lampiranPreviewContainer');
    const previewList = document.getElementById('lampiranPreviewList');
    const imageCountText = document.getElementById('imageCountText');
    const inputElement = event.target;
    
    // Validasi jumlah file
    if (files.length > 3) {
        alert('Maksimal 3 gambar yang dapat diupload');
        inputElement.value = '';
        previewContainer.classList.add('hidden');
        return;
    }
    
    if (files.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    // Clear previous previews
    previewList.innerHTML = '';
    
    let validFiles = [];
    
    files.forEach((file, index) => {
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert(`File "${file.name}" bukan format gambar yang valid (JPG/PNG)`);
            return;
        }
        
        // Validasi ukuran file (max 10MB sebelum compress)
        if (file.size > 10 * 1024 * 1024) {
            alert(`File "${file.name}" terlalu besar (max 10MB)`);
            return;
        }
        
        validFiles.push(file);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.className = 'relative';
            previewItem.innerHTML = `
                <img src="${e.target.result}" 
                     class="w-full h-32 object-cover rounded-lg border-2 border-gray-300 shadow-sm" 
                     alt="Preview ${index + 1}">
                <button type="button" 
                        onclick="removePreviewImage(${index})" 
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition shadow-lg"
                        title="Hapus gambar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="text-center mt-1">
                    <span class="text-xs text-gray-500">${(file.size / 1024).toFixed(0)} KB</span>
                </div>
            `;
            previewList.appendChild(previewItem);
        }
        reader.readAsDataURL(file);
    });
    
    // Update counter
    imageCountText.textContent = `${validFiles.length} gambar dipilih`;
    
    if (validFiles.length > 0) {
        previewContainer.classList.remove('hidden');
    } else {
        inputElement.value = '';
        previewContainer.classList.add('hidden');
    }
}

// Fungsi untuk menghapus preview individual
function removePreviewImage(index) {
    const inputElement = document.getElementById('lampiranInput');
    const dt = new DataTransfer();
    const files = Array.from(inputElement.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    inputElement.files = dt.files;
    
    // Trigger preview update
    previewMultipleLampiran({ target: inputElement });
}
</script>
@endsection