<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambah role direktur ke enum users
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sales', 'sales_executive', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager', 'direktur') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sales', 'sales_executive', 'approver1', 'approver2', 'approver3', 'approver4', 'approver5', 'approver6', 'viewer', 'piutang_manager') NOT NULL");
    }
};
