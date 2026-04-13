<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spt_tujuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spt_id');
            $table->string('jenis_tujuan');
            $table->string('poktan_nama', 30)->nullable();
            $table->text('deskripsi_kota')->nullable();
            $table->text('deskripsi_lainnya')->nullable();
            $table->timestamps();

            $table->foreign('spt_id')
                ->references('id')
                ->on('spts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spt_tujuan');
    }
};
