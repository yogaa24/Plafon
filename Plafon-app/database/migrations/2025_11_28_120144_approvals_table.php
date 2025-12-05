<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users');
            $table->integer('level');
            $table->enum('status', ['approved', 'rejected', 'revision']);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->decimal('piutang', 15, 2)->nullable()->after('note');
            $table->decimal('jml_over', 15, 2)->nullable()->after('piutang');
            $table->decimal('jml_od_30', 15, 2)->nullable()->after('jml_over');
            $table->decimal('jml_od_60', 15, 2)->nullable()->after('jml_od_30');
            $table->decimal('jml_od_90', 15, 2)->nullable()->after('jml_od_60');
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
};
