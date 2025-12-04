<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->decimal('piutang', 15, 2)->default(0)->after('note');
            $table->unsignedInteger('jml_over')->default(0)->after('piutang');
            $table->unsignedInteger('jml_od_30')->default(0)->after('jml_over');
            $table->unsignedInteger('jml_od_60')->default(0)->after('jml_od_30');
            $table->unsignedInteger('jml_od_90')->default(0)->after('jml_od_60');

            // Bonus: tambah unique constraint biar lebih rapi (opsional tapi sangat disarankan)
            $table->unique(['submission_id', 'level']);
        });
    }

    public function down()
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropUnique(['submission_id', 'level']); // kalau pakai unique
            $table->dropColumn(['piutang', 'jml_over', 'jml_od_30', 'jml_od_60', 'jml_od_90']);
        });
    }
};