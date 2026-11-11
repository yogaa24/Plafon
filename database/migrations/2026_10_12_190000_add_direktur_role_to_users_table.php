<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambah role direktur ke enum users
        $table = DB::getTablePrefix() . 'users';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN role ENUM('sales', 'sales_executive', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager', 'direktur') NOT NULL");
    }

    public function down()
    {
        $table = DB::getTablePrefix() . 'users';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN role ENUM('sales', 'sales_executive', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager') NOT NULL");
    }
};
