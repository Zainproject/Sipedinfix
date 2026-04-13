<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dana_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('sumber_dana', 100);
            $table->decimal('nominal', 20, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dana_masuk');
    }
};
