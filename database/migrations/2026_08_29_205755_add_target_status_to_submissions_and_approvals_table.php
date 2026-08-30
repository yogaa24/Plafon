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
            if (!Schema::hasColumn('submissions', 'target_status')) {
                $table->string('target_status', 50)->nullable()->after('rejection_note');
            }
        });

        Schema::table('approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('approvals', 'target_status')) {
                $table->string('target_status', 50)->nullable()->after('note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'target_status')) {
                $table->dropColumn('target_status');
            }
        });

        Schema::table('approvals', function (Blueprint $table) {
            if (Schema::hasColumn('approvals', 'target_status')) {
                $table->dropColumn('target_status');
            }
        });
    }
};
