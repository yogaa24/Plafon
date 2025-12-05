<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_level3_approver')->default(false)
                ->after('role')
                ->comment('Flag untuk approver level 3 (Fairin, Vita, Diana, Direktur)');
            
            $table->string('approver_name')->nullable()
                ->after('is_level3_approver')
                ->comment('Nama approver untuk ditampilkan (Fairin/Vita/Diana/Direktur)');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_level3_approver', 'approver_name']);
        });
    }
};