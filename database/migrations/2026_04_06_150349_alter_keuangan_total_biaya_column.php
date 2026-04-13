<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->decimal('total_biaya', 20, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->decimal('total_biaya', 15, 2)->default(0)->change();
        });
    }
};
