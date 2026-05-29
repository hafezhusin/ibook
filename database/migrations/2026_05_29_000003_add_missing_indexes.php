<?php

/**
 * iBook — Database Audit Fix #3
 * Tambah index yang hilang + buang index yang redundan.
 *
 * TAMBAH (index baru):
 *   - idx_tempahan_created_at          : untuk filter 'baharu 24 jam' dalam worklist
 *   - idx_audit_chain                  : untuk AuditLogger prev_hash lookup (setiap INSERT audit)
 *   - idx_audit_tindakan_masa          : untuk laporan audit mengikut tindakan + tarikh
 *   - idx_users_aktif_peranan          : untuk pengurusan pengguna (aktif + peranan filter)
 *
 * BUANG (index redundan):
 *   - tempahan_bilik_id_foreign (bilik_id) : dilindungi oleh idx_tempahan_bilik_tarikh_sesi_status
 *   - tempahan_user_id_foreign (user_id)   : dilindungi oleh idx_tempahan_user_tarikh_status
 *
 * NOTA: idx_tempahan_bilik_tarikh_sesi_status bermula dengan bilik_id → melindungi
 *       lookup bilik_id tunggal (prefix rule). Begitu juga idx_tempahan_user_tarikh_status.
 *       Index single-column yang berasingan adalah mubazir dan memperlahankan INSERT.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. tempahan: tambah idx_tempahan_created_at ────────────────────
        Schema::table('tempahan', function (Blueprint $table) {
            $table->index('created_at', 'idx_tempahan_created_at');
        });

        // ── 2. tempahan: buang index redundan (MySQL sahaja) ──────────────
        // Nota: Index ini wujud dalam MySQL sahaja (auto-dibuat bersama FK constraint).
        // SQLite tidak mempunyai index ini — guard dengan getDriverName().
        if (DB::getDriverName() === 'mysql') {
            Schema::table('tempahan', function (Blueprint $table) {
                $table->dropIndex('tempahan_bilik_id_foreign');
                $table->dropIndex('tempahan_user_id_foreign');
            });
        }

        // ── 3. activity_log: tambah composite index untuk hash chain ───────
        // AuditLogger::catat() melakukan:
        //   ActivityLog::latest('dicipta_pada')->latest('id')->value('record_hash')
        // Query ini memerlukan (dicipta_pada DESC, id DESC) — bukan single dicipta_pada
        // Nota: MySQL B-Tree index tidak sokong DESC secara eksplisit pre-8.0,
        //       tapi MySQL 8.0+ sokong index direction — gunakan standard ASC
        //       dan MySQL akan scan in reverse order.
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['dicipta_pada', 'id'], 'idx_audit_chain');
            $table->index(['tindakan', 'dicipta_pada'], 'idx_audit_tindakan_masa');
        });

        // ── 4. users: tambah composite index aktif + peranan ──────────────
        Schema::table('users', function (Blueprint $table) {
            $table->index(['aktif', 'peranan'], 'idx_users_aktif_peranan');
        });
    }

    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropIndex('idx_tempahan_created_at');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('tempahan', function (Blueprint $table) {
                // Pulihkan index yang dipadam (MySQL sahaja)
                $table->index('bilik_id', 'tempahan_bilik_id_foreign');
                $table->index('user_id', 'tempahan_user_id_foreign');
            });
        }

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('idx_audit_chain');
            $table->dropIndex('idx_audit_tindakan_masa');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_aktif_peranan');
        });
    }
};
