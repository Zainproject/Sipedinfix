<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->json('detail_petugas')->nullable()->after('nomor_kwitansi');

            $table->dropColumn([
                'keterangan_biaya',
                'harga_biaya',
                'subtotal_perhari',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->json('keterangan_biaya')->nullable();
            $table->json('harga_biaya')->nullable();
            $table->decimal('subtotal_perhari', 15, 2)->nullable();

            $table->dropColumn('detail_petugas');
        });
    }
};
