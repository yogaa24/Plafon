<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Ubah enum role untuk menambahkan approver4, approver5, approver6
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sales', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer') NOT NULL");
    }

    public function down()
    {
        // Kembalikan ke enum lama
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sales', 'approver1', 'approver2', 'approver3', 'viewer') NOT NULL");
    }
};