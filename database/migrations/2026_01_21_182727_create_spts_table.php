<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spts', function (Blueprint $table) {
            $table->id();

            // DATA SPT - DIISI SEKRETARIS
            $table->string('nomor_surat')->unique();
            $table->string('alat_angkut');
            $table->string('berangkat_dari');
            $table->text('keperluan');

            $table->date('tanggal_berangkat');
            $table->date('tanggal_kembali');
            $table->integer('total_hari');

            $table->integer('bulan');
            $table->integer('tahun');

            $table->text('kehadiran');
            $table->text('arahan');
            $table->text('masalah_temuan');
            $table->text('saran_tindakan');
            $table->text('lain_lain')->nullable();

            // STATUS PROSES BENDAHARA
            $table->string('status_bendahara')->default('belum diisi bendahara');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spts');
    }
};
