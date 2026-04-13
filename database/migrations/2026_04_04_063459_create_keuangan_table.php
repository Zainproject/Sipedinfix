<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spt_id')->constrained('spts')->onDelete('cascade');
            $table->string('mak')->nullable();
            $table->string('nomor_kwitansi')->nullable();
            $table->json('keterangan_biaya')->nullable();
            $table->json('harga_biaya')->nullable();
            $table->decimal('subtotal_perhari', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
