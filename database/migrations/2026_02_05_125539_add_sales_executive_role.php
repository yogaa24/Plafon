<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambah role sales_executive ke enum
        $table = DB::getTablePrefix() . 'users';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN role ENUM('sales', 'sales_executive', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager') NOT NULL");
    }

    public function down()
    {
        $table = DB::getTablePrefix() . 'users';
        DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN role ENUM('sales', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager') NOT NULL");
    }
};