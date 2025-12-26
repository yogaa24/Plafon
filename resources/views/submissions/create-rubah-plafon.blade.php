@extends('layouts.app')

@section('title', 'Rubah Plafon Customer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rubah Plafon Customer</h1>
            <p class="text-sm text-gray-600 mt-1">Buat pengajuan perubahan plafon untuk customer yang sudah ada</p>
        </div>
        <a href="{{ route('submissions.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
            ← Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('submissions.store') }}" method="POST" id="rubahPlafonForm" enctype="multipart/form-data">
            @csrf
            
            <!-- Hidden fields -->
            <input type="hidden" name="plafon_type" value="rubah">
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            <input type="hidden" name="nama" value="{{ $customer->nama }}">
            <input type="hidden" name="nama_kios" value="{{ $customer->nama_kios }}">
            <input type="hidden" name="alamat" value="{{ $customer->plafon_aktif }}">

            <!-- Kode (Read Only) -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Pengajuan <span class="text-red-500">*</span>
                </label>
                <input type="text" value="{{ $kode }}" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-mono">
                <p class="text-xs text-gray-500 mt-1">Kode akan digenerate otomatis saat pengajuan disimpan</p>
            </div>

            <!-- Read-Only Fields -->
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
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengajuan Plafon Baru</h3>
                
                <!-- Checkbox Naik/Turun Plafon -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        Jenis Perubahan Plafon <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-6">
                        <label class="flex items-center cursor-pointer group">
                            <input 
                                type="radio" 
                                name="plafon_direction" 
                                value="naik" 
                                {{ old('plafon_direction') == 'naik' ? 'checked' : '' }}
                                required
                                class="w-5 h-5 text-green-600 border-gray-300 focus:ring-green-500"
                                onchange="updatePlafonDirection('naik')">
                            <span class="ml-3 flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                <span class="font-medium text-gray-700 group-hover:text-green-600">Naik Plafon</span>
                            </span>
                        </label>
                        <label class="flex items-center cursor-pointer group">
                            <input 
                                type="radio" 
                                name="plafon_direction" 
                                value="turun" 
                                {{ old('plafon_direction') == 'turun' ? 'checked' : '' }}
                                required
                                class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-500"
                                onchange="updatePlafonDirection('turun')">
                            <span class="ml-3 flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                <span class="font-medium text-gray-700 group-hover:text-red-600">Turun Plafon</span>
                            </span>
                        </label>
                    </div>
                    @error('plafon_direction')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Plafon Saat Ini -->
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
                                class="w-full pl-12 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-gray-700 font-semibold">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Plafon yang sedang berlaku</p>
                    </div>

                    <!-- Plafon yang Diusulkan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Plafon yang Diusulkan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-gray-500">Rp</span>
                            <input 
                                type="number" 
                                id="plafonUsulan"
                                name="plafon" 
                                value="{{ old('plafon', $customer->plafon_aktif) }}" 
                                required 
                                min="0" 
                                step="1000"
                                class="w-full pl-12 pr-4 py-2.5 border @error('plafon') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                placeholder="Masukkan plafon usulan"
                                oninput="calculateDifference()">
                        </div>
                        @error('plafon')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="plafonDifference" class="text-xs mt-1 font-medium hidden">
                            <!-- Will be populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Info Selisih Plafon -->
                <div id="selisihInfo" class="mt-4 p-4 rounded-lg border hidden">
                    <div class="flex items-center">
                        <svg id="selisihIcon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="selisihText" class="text-sm font-medium"></span>
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
                        class="w-full px-4 py-2.5 border @error('komitmen_pembayaran') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Contoh: Pembayaran setiap hari Senin dan Kamis">{{ old('komitmen_pembayaran') }}</textarea>
                    @error('komitmen_pembayaran')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
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
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Ajukan Rubah Plafon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const plafonSaatIni = {{ $customer->plafon_aktif }};

function updatePlafonDirection(direction) {
    const plafonUsulan = document.getElementById('plafonUsulan');
    const selisihInfo = document.getElementById('selisihInfo');
    
    // Update border color based on direction
    if (direction === 'naik') {
        plafonUsulan.classList.remove('border-red-300');
        plafonUsulan.classList.add('border-green-300');
    } else {
        plafonUsulan.classList.remove('border-green-300');
        plafonUsulan.classList.add('border-red-300');
    }
    
    // Calculate difference if value exists
    calculateDifference();
}

function calculateDifference() {
    const plafonUsulanInput = document.getElementById('plafonUsulan');
    const plafonUsulan = parseInt(plafonUsulanInput.value) || 0;
    const difference = plafonUsulan - plafonSaatIni;
    const plafonDifferenceEl = document.getElementById('plafonDifference');
    const selisihInfoEl = document.getElementById('selisihInfo');
    const selisihTextEl = document.getElementById('selisihText');
    const selisihIconEl = document.getElementById('selisihIcon');
    
    if (plafonUsulan === 0) {
        plafonDifferenceEl.classList.add('hidden');
        selisihInfoEl.classList.add('hidden');
        return;
    }
    
    const selectedDirection = document.querySelector('input[name="plafon_direction"]:checked');
    
    if (!selectedDirection) {
        plafonDifferenceEl.classList.add('hidden');
        selisihInfoEl.classList.add('hidden');
        return;
    }
    
    const direction = selectedDirection.value;
    const absValue = Math.abs(difference);
    const formattedDiff = new Intl.NumberFormat('id-ID').format(absValue);
    
    // Show difference below input
    plafonDifferenceEl.classList.remove('hidden');
    
    if (difference > 0) {
        plafonDifferenceEl.className = 'text-xs mt-1 font-medium text-green-600';
        plafonDifferenceEl.textContent = `↑ Naik Rp ${formattedDiff}`;
        
        // Validate direction
        if (direction === 'turun') {
            showWarning('Plafon usulan lebih besar dari plafon saat ini. Harap pilih "Naik Plafon"');
            return;
        }
    } else if (difference < 0) {
        plafonDifferenceEl.className = 'text-xs mt-1 font-medium text-red-600';
        plafonDifferenceEl.textContent = `↓ Turun Rp ${formattedDiff}`;
        
        // Validate direction
        if (direction === 'naik') {
            showWarning('Plafon usulan lebih kecil dari plafon saat ini. Harap pilih "Turun Plafon"');
            return;
        }
    } else {
        plafonDifferenceEl.className = 'text-xs mt-1 font-medium text-gray-600';
        plafonDifferenceEl.textContent = 'Tidak ada perubahan';
        selisihInfoEl.classList.add('hidden');
        return;
    }
    
    // Show summary info
    selisihInfoEl.classList.remove('hidden');
    
    if (difference > 0) {
        selisihInfoEl.className = 'mt-4 p-4 rounded-lg border bg-green-50 border-green-300';
        selisihIconEl.className = 'w-5 h-5 mr-2 text-green-600';
        selisihTextEl.className = 'text-sm font-medium text-green-800';
        selisihTextEl.innerHTML = `Pengajuan <strong>NAIK</strong> plafon sebesar Rp ${formattedDiff}`;
    } else {
        selisihInfoEl.className = 'mt-4 p-4 rounded-lg border bg-red-50 border-red-300';
        selisihIconEl.className = 'w-5 h-5 mr-2 text-red-600';
        selisihTextEl.className = 'text-sm font-medium text-red-800';
        selisihTextEl.innerHTML = `Pengajuan <strong>TURUN</strong> plafon sebesar Rp ${formattedDiff}`;
    }
}

function showWarning(message) {
    const selisihInfoEl = document.getElementById('selisihInfo');
    const selisihTextEl = document.getElementById('selisihText');
    const selisihIconEl = document.getElementById('selisihIcon');
    
    selisihInfoEl.classList.remove('hidden');
    selisihInfoEl.className = 'mt-4 p-4 rounded-lg border bg-orange-50 border-orange-300';
    selisihIconEl.className = 'w-5 h-5 mr-2 text-orange-600';
    selisihTextEl.className = 'text-sm font-medium text-orange-800';
    selisihTextEl.innerHTML = `⚠️ ${message}`;
}

// Validate before submit
document.getElementById('rubahPlafonForm').addEventListener('submit', function(e) {
    const plafonUsulan = parseInt(document.getElementById('plafonUsulan').value) || 0;
    const difference = plafonUsulan - plafonSaatIni;
    const selectedDirection = document.querySelector('input[name="plafon_direction"]:checked');
    
    if (!selectedDirection) {
        e.preventDefault();
        alert('Silakan pilih jenis perubahan plafon (Naik/Turun)');
        return;
    }
    
    const direction = selectedDirection.value;
    
    // Validate direction matches actual change
    if (direction === 'naik' && difference <= 0) {
        e.preventDefault();
        alert('Plafon usulan harus lebih besar dari plafon saat ini untuk pilihan "Naik Plafon"');
        return;
    }
    
    if (direction === 'turun' && difference >= 0) {
        e.preventDefault();
        alert('Plafon usulan harus lebih kecil dari plafon saat ini untuk pilihan "Turun Plafon"');
        return;
    }
    
    // Confirmation
    const formattedUsulan = new Intl.NumberFormat('id-ID').format(plafonUsulan);
    const formattedSaatIni = new Intl.NumberFormat('id-ID').format(plafonSaatIni);
    const actionText = direction === 'naik' ? 'NAIK' : 'TURUN';
    
    const confirmed = confirm(
        `Konfirmasi Pengajuan Rubah Plafon:\n\n` +
        `Plafon Saat Ini: Rp ${formattedSaatIni}\n` +
        `Plafon Usulan: Rp ${formattedUsulan}\n` +
        `Jenis: ${actionText} PLAFON\n\n` +
        `Lanjutkan pengajuan?`
    );
    
    if (!confirmed) {
        e.preventDefault();
    }
});

// Initialize on load if old values exist
document.addEventListener('DOMContentLoaded', function() {
    const selectedDirection = document.querySelector('input[name="plafon_direction"]:checked');
    if (selectedDirection) {
        updatePlafonDirection(selectedDirection.value);
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