<?php

/**
 * iBook — Score 100/100 Multi-Tenant Foundation #19
 * Tambah bahagian_id pada bilik_mesyuarat dan users.
 *
 * STRATEGI DATA:
 *   bilik_mesyuarat → semua bilik sedia ada → bahagian_id = 1 (BPTM)
 *   users           → staf/urus_setia sedia ada → bahagian_id = 1 (BPTM)
 *                  → pentadbir_sistem → bahagian_id = NULL (merentas semua bahagian)
 *
 * NULLABLE:
 *   Kedua-dua kolum nullable untuk toleransi data lama dan SQLite test suite.
 *   NULL bermaksud "milik bahagian utama" dalam logik scoping.
 *
 * NOTA: Trigger tidak disokong di InfinityFree — scoping dilakukan di lapisan
 * aplikasi (BilikMesyuarat::scopeUntukPengguna) bukan di DB.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── bilik_mesyuarat ──────────────────────────────────────────
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->foreignId('bahagian_id')
                  ->nullable()
                  ->after('id')
                  ->comment('NULL = milik bahagian utama')
                  ->constrained('bahagian')
                  ->restrictOnDelete();

            $table->index(['bahagian_id', 'status'], 'idx_bilik_bahagian_status');
        });

        // Pautan semua bilik sedia ada → BPTM
        DB::table('bilik_mesyuarat')->whereNull('bahagian_id')->update(['bahagian_id' => 1]);

        // ── users ────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bahagian_id')
                  ->nullable()
                  ->after('id')
                  ->comment('NULL = pentadbir_sistem (merentas semua bahagian)')
                  ->constrained('bahagian')
                  ->nullOnDelete();

            $table->index(['bahagian_id', 'peranan'], 'idx_user_bahagian_peranan');
        });

        // pentadbir_sistem → NULL (akses merentas semua bahagian)
        // staf + urus_setia → BPTM
        DB::table('users')
            ->where('peranan', '!=', 'pentadbir_sistem')
            ->whereNull('bahagian_id')
            ->update(['bahagian_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_bahagian_peranan');
            $table->dropConstrainedForeignId('bahagian_id');
        });

        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->dropIndex('idx_bilik_bahagian_status');
            $table->dropConstrainedForeignId('bahagian_id');
        });
    }
};
