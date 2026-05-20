<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->string('dikemaskini_oleh')->nullable()->after('lokasi');
            $table->timestamp('dikemaskini_pada')->nullable()->after('dikemaskini_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->dropColumn(['dikemaskini_oleh', 'dikemaskini_pada']);
        });
    }
};
