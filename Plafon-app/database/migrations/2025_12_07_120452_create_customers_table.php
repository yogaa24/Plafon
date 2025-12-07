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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade'); // Sales yang handle customer ini
            $table->string('kode_customer')->unique(); // Kode unik customer
            $table->string('nama');
            $table->string('nama_kios');
            $table->text('alamat');
            $table->decimal('plafon_aktif', 15, 2)->default(0); // Plafon yang sedang aktif
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            // Indexes
            $table->index('sales_id');
            $table->index('nama');
            $table->index('nama_kios');
            $table->index('status');
            $table->index(['sales_id', 'status']); // Composite index untuk query by sales & status
        });

        // Tambahkan kolom customer_id di tabel submissions untuk relasi
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('sales_id')->constrained('customers')->onDelete('cascade');
            
            // Index untuk performa
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        
        Schema::dropIfExists('customers');
    }
};