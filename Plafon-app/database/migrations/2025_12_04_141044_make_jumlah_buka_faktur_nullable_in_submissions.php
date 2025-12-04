<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('jumlah_buka_faktur')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('jumlah_buka_faktur')->nullable(false)->change();
        });
    }
};
