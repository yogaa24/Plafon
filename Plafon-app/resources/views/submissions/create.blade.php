@extends('layouts.app')

@section('title', 'Buat Pengajuan Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('submissions.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">Buat Pengajuan Baru</h1>
            <p class="text-indigo-100 text-sm mt-1">Isi formulir di bawah untuk membuat pengajuan penjualan</p>
        </div>

        <form action="{{ route('submissions.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Kode Otomatis (Read-only) -->
            <div class="mb-6 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                <div class="flex items-start">
                    <div>
                        <p class="text-sm font-bold text-black mt-1">{{ $kode }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama with Select2 -->
                <div>
                    <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <select name="nama" id="nama" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('nama') border-red-500 @enderror">
                        <option value="">Ketik untuk mencari atau masukkan nama baru...</option>
                    </select>
                    
                    <p class="mt-1 text-xs text-gray-500">💡 Ketik minimal 2 karakter untuk mencari data yang sudah ada</p>
                    
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Kios -->
                <div>
                    <label for="nama_kios" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Kios <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kios" id="nama_kios" value="{{ old('nama_kios') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('nama_kios') border-red-500 @enderror">
                    @error('nama_kios')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-semibold text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alamat" id="alamat" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plafon -->
                <div>
                    <label for="plafon" class="block text-sm font-semibold text-gray-700 mb-2">
                        Plafon (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="plafon" id="plafon" value="{{ old('plafon') }}" min="0" step="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('plafon') border-red-500 @enderror"
                        placeholder="Contoh: 5000000">
                    @error('plafon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan nilai dalam rupiah tanpa titik/koma</p>
                </div>

                <!-- Jumlah Buka Faktur -->
                <div>
                    <label for="jumlah_buka_faktur" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jumlah Buka Faktur <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah_buka_faktur" id="jumlah_buka_faktur" value="{{ old('jumlah_buka_faktur') }}" min="1" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('jumlah_buka_faktur') border-red-500 @enderror"
                        placeholder="Contoh: 5">
                    @error('jumlah_buka_faktur')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Komitmen Pembayaran -->
                <div class="md:col-span-2">
                    <label for="komitmen_pembayaran" class="block text-sm font-semibold text-gray-700 mb-2">
                        Komitmen Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <textarea name="komitmen_pembayaran" id="komitmen_pembayaran" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('komitmen_pembayaran') border-red-500 @enderror"
                        placeholder="Contoh: Pembayaran akan dilakukan setiap tanggal 25 setiap bulan">{{ old('komitmen_pembayaran') }}</textarea>
                    @error('komitmen_pembayaran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('submissions.index') }}" 
                    class="px-6 py-2.5 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                    Simpan Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#nama').select2({
        placeholder: 'Ketik untuk mencari atau masukkan nama baru...',
        allowClear: true,
        tags: true, // Allow creating new entries
        minimumInputLength: 2,
        ajax: {
            url: '/submissions/search-nama',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                console.log('Search results:', data); // Debug log
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.nama,
                            text: item.nama,
                            nama_kios: item.nama_kios,
                            alamat: item.alamat,
                            plafon: item.plafon,
                            sales_name: item.sales_name,
                            is_own: item.is_own,
                            created_at: item.created_at
                        };
                    })
                };
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
            },
            cache: true
        },
        templateResult: formatResult,
        templateSelection: formatSelection,
        language: {
            searching: function() {
                return 'Mencari...';
            },
            noResults: function() {
                return 'Tidak ada data. Ketik untuk membuat baru.';
            },
            inputTooShort: function() {
                return '';
            },
            errorLoading: function() {
                return 'Gagal memuat data. Silakan coba lagi.';
            }
        }
    });

    // Custom template for result dropdown
    function formatResult(item) {
        if (!item.id || item.loading) {
            return item.text;
        }

        // Jika item baru (tidak ada di database)
        if (!item.nama_kios) {
            return $('<div class="py-1 text-sm font-normal">Buat baru: ' + item.text + '</div>');
        }

        // Tampilkan hanya nama
        var $result = $(
            '<div class="select2-result-item" style="padding: 6px 0;">' +
                '<strong style="color: #111827; font-size: 14px;">' + item.text + '</strong>' +
            '</div>'
        );

        return $result;
    }

    // Template for selected item
    function formatSelection(item) {
        return item.text;
    }

    // Event: When selection changes
    $('#nama').on('select2:select', function (e) {
        var data = e.params.data;
        
        // Only auto-fill if it's existing data (not a new tag)
        if (data.nama_kios && data.alamat) {
            // Fill form fields
            $('#nama_kios').val(data.nama_kios);
            $('#alamat').val(data.alamat);
            
            // parseInt untuk buang desimal .00
            var plafonValue = data.plafon ? parseInt(data.plafon) : '';
            $('#plafon').val(plafonValue);

            // Visual feedback
            flashField('#nama_kios');
            flashField('#alamat');
            flashField('#plafon');

            // Focus on next field
            setTimeout(function() {
                $('#jumlah_buka_faktur').focus();
            }, 100);

            // Show success message
            showSuccessMessage('Data berhasil diisi otomatis! Silakan isi jumlah buka faktur dan komitmen pembayaran.');
        }
    });

    // Flash field effect
    function flashField(selector) {
        var $field = $(selector);
        $field.addClass('border-green-500 bg-green-50');
        setTimeout(function() {
            $field.removeClass('border-green-500 bg-green-50');
        }, 2000);
    }

    // Success message
    function showSuccessMessage(message) {
        var $alert = $(
            '<div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" style="animation: slideUp 0.3s ease-out;">' +
                '<div style="display: flex; align-items: center;">' +
                    '<svg style="width: 20px; height: 20px; margin-right: 8px;" fill="currentColor" viewBox="0 0 20 20">' +
                        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' +
                    '</svg>' +
                    message +
                '</div>' +
            '</div>'
        );

        $('body').append($alert);

        setTimeout(function() {
            $alert.fadeOut(500, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>

<style>
/* Custom Select2 Styling */
.select2-container--default .select2-selection--single {
    height: 42px !important;
    padding: 6px 12px !important;
    border: 1px solid #D1D5DB !important;
    border-radius: 0.5rem !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
    right: 8px !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #6366F1 !important;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
}

.select2-dropdown {
    border: 1px solid #D1D5DB !important;
    border-radius: 0.5rem !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
}

.select2-results__option {
    padding: 0 12px !important;
}

.select2-results__option--highlighted {
    background-color: #EEF2FF !important;
    color: #111827 !important;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #E0E7FF !important;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #D1D5DB !important;
    border-radius: 0.375rem !important;
    padding: 8px 12px !important;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #6366F1 !important;
    outline: none !important;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
@endsection