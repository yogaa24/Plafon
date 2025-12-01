<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('nama_kios');
            $table->text('alamat');
            $table->decimal('plafon', 15, 2);
            $table->integer('jumlah_buka_faktur');
            $table->text('komitmen_pembayaran');
            $table->foreignId('sales_id')->constrained('users');
            $table->enum('status', ['pending', 'approved_1', 'approved_2', 'approved_3', 'rejected', 'revision','done'])->default('pending');
            $table->integer('current_level')->default(1);
            $table->text('revision_note')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('submissions');
    }
};
