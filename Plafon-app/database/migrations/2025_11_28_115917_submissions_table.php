<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            
            // Data Customer
            $table->string('nama');
            $table->string('nama_kios');
            $table->text('alamat');
            
            // Data Plafon
            $table->decimal('plafon', 15, 2);
            $table->enum('plafon_type', ['open', 'rubah'])->default('open')->comment('Jenis pengajuan: open=baru, rubah=perubahan');
            $table->enum('plafon_direction', ['naik', 'turun'])->nullable()->comment('Arah perubahan plafon (hanya untuk type=rubah)');
            $table->unsignedBigInteger('previous_submission_id')->nullable()->comment('ID submission sebelumnya (untuk rubah plafon)');
            
            // Data Faktur & Komitmen
            $table->integer('jumlah_buka_faktur');
            $table->text('komitmen_pembayaran');
            
            // Relasi Sales
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade');
            
            // Status & Approval
            $table->enum('status', [
                'pending',      // Menunggu approval level 1
                'approved_1',   // Disetujui level 1, menunggu level 2
                'approved_2',   // Disetujui level 2, menunggu level 3
                'approved_3',   // Disetujui level 3, proses input
                'rejected',     // Ditolak
                'revision',     // Perlu revisi
                'done'          // Selesai/Aktif
            ])->default('pending');
            $table->integer('current_level')->default(1)->comment('Level approval saat ini (1-3)');
            
            // Notes
            $table->text('revision_note')->nullable();
            $table->text('rejection_note')->nullable();
            
            $table->timestamps();
            
            // Foreign Key untuk previous_submission_id
            $table->foreign('previous_submission_id')
                  ->references('id')
                  ->on('submissions')
                  ->onDelete('set null');
            
            // Indexes untuk performa
            $table->index('sales_id');
            $table->index('status');
            $table->index('plafon_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};