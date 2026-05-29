<?php

/**
 * iBook — Score 10/10 Fix #12
 * Optimum index akhir — buang redundan, tambah yang hilang.
 *
 * BUANG (redundan):
 *   activity_log_tindakan_index(tindakan)
 *     → dilindungi oleh idx_audit_tindakan_masa(tindakan, dicipta_pada) [prefix rule]
 *   activity_log_dicipta_pada_index(dicipta_pada)
 *     → dilindungi oleh idx_audit_chain(dicipta_pada, id) [prefix rule]
 *   Membuang kedua-dua mengurangkan overhead INSERT pada activity_log.
 *
 * TAMBAH:
 *   idx_tb_tarikh (tempahan_berulang.tarikh_mula, tarikh_tamat)
 *     → untuk query cari kumpulan berulang yang aktif pada tarikh tertentu
 *   idx_backup_jenis_tarikh (backup_log.jenis, created_at)
 *     → untuk filter backup mengikut jenis dan tarikh dalam UI pengurusan
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Buang index redundan (MySQL sahaja — nama index MySQL-specific) ──
        if (DB::getDriverName() === 'mysql') {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropIndex('activity_log_tindakan_index');
                $table->dropIndex('activity_log_dicipta_pada_index');
            });
        }

        // ── Tambah index baru ─────────────────────────────────────────────
        Schema::table('tempahan_berulang', function (Blueprint $table) {
            $table->index(['tarikh_mula', 'tarikh_tamat'], 'idx_tb_tarikh');
        });

        Schema::table('backup_log', function (Blueprint $table) {
            $table->index(['jenis', 'created_at'], 'idx_backup_jenis_tarikh');
        });
    }

    public function down(): void
    {
        Schema::table('tempahan_berulang', function (Blueprint $table) {
            $table->dropIndex('idx_tb_tarikh');
        });

        Schema::table('backup_log', function (Blueprint $table) {
            $table->dropIndex('idx_backup_jenis_tarikh');
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->index('tindakan', 'activity_log_tindakan_index');
                $table->index('dicipta_pada', 'activity_log_dicipta_pada_index');
            });
        }
    }
};
