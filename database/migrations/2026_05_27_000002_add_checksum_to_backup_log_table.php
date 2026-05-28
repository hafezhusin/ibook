<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_log', function (Blueprint $table) {
            // SHA-256 hash (64 hex chars) — untuk semak integriti fail backup
            $table->string('checksum', 64)->nullable()->after('saiz_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('backup_log', function (Blueprint $table) {
            $table->dropColumn('checksum');
        });
    }
};
