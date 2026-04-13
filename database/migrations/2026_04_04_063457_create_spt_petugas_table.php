<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spt_petugas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spt_id');
            $table->string('nip_petugas');
            $table->timestamps();

            $table->foreign('spt_id')
                ->references('id')
                ->on('spts')
                ->onDelete('cascade');

            $table->foreign('nip_petugas')
                ->references('nip')
                ->on('petugas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spt_petugas');
    }
};
