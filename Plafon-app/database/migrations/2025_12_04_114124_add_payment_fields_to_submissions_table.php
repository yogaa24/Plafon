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
        Schema::table('submissions', function (Blueprint $table) {
            // Kolom untuk jenis pembayaran (OD atau Over)
            $table->enum('payment_type', ['od', 'over'])->nullable()->after('komitmen_pembayaran');
            
            // Kolom untuk menyimpan detail pembayaran dalam format JSON
            $table->json('payment_data')->nullable()->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'payment_data']);
        });
    }
};