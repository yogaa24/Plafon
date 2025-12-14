<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update enum status untuk menambahkan status baru
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM(
            'pending',
            'approved_1',
            'approved_2',
            'approved_3',
            'approver_4',
            'approver_5',
            'approver_6',
            'pending_viewer',
            'rejected',
            'done'
        ) NOT NULL DEFAULT 'pending'");

        // Update current_level - ubah ke integer atau nullable
        // Opsi 1: Ubah ke integer
        DB::statement("ALTER TABLE submissions MODIFY COLUMN current_level INT NULL");
        
        // Atau Opsi 2: Tetap integer tapi nullable dengan default NULL
        // Schema::table('submissions', function (Blueprint $table) {
        //     $table->integer('current_level')->nullable()->default(1)->change();
        // });
    }

    public function down()
    {
        // Kembalikan ke enum lama
        DB::statement("ALTER TABLE submissions MODIFY COLUMN status ENUM(
            'pending',
            'approved_1',
            'approved_2',
            'approved_3',
            'rejected',
            'done'
        ) NOT NULL DEFAULT 'pending'");
    }
};