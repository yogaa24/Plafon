// database/migrations/2026_01_01_add_plafon_sebelumnya_to_submissions.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('plafon_sebelumnya', 15, 2)->nullable()->after('plafon');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('plafon_sebelumnya');
        });
    }
};