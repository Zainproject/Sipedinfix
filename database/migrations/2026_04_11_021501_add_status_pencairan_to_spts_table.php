<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spts', function (Blueprint $table) {
            $table->string('status_pencairan', 30)
                ->nullable()
                ->after('status_bendahara');
        });
    }

    public function down(): void
    {
        Schema::table('spts', function (Blueprint $table) {
            $table->dropColumn('status_pencairan');
        });
    }
};
