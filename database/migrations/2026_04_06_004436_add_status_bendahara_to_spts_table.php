<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spts', function (Blueprint $table) {
            if (!Schema::hasColumn('spts', 'status_bendahara')) {
                $table->string('status_bendahara')->nullable()->after('lain_lain');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spts', function (Blueprint $table) {
            if (Schema::hasColumn('spts', 'status_bendahara')) {
                $table->dropColumn('status_bendahara');
            }
        });
    }
};
