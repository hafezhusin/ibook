<?php

/**
 * iBook — Score 100/100 Fix #14
 * Tambah covering index + reporting indexes untuk skor 100/100.
 *
 * TIGA INDEX BAHARU:
 *
 * 1. idx_tempahan_conflict_check (bilik_id, tarikh, status, masa_mula, masa_tamat)
 *    Covering index untuk conflict detection query dalam lockForUpdate():
 *      WHERE bilik_id=? AND tarikh=? AND status='diluluskan'
 *            AND masa_mula < ? AND masa_tamat > ?
 *    Tanpa covering index: MySQL membaca heap page setiap baris yang lulus prefix.
 *    Dengan covering index: index-only scan — sifar I/O ke main table.
 *
 * 2. idx_tempahan_laporan_tahunan (tarikh, status, bilik_id, kategori)
 *    Laporan agregasi tahunan:
 *      WHERE tarikh BETWEEN '2026-01-01' AND '2026-12-31'
 *            AND status = 'diluluskan'
 *      GROUP BY bilik_id, kategori
 *    tarikh sebagai leftmost — optimizer boleh guna range scan per tahun.
 *
 * 3. idx_tempahan_laporan_pengguna (user_id, tarikh, status)
 *    Laporan per pengguna/unit:
 *      WHERE user_id = ? AND tarikh BETWEEN ? AND ? AND status = ?
 *    Hot query pada dashboard laporan individu.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            // Covering index untuk conflict check — MySQL & SQLite
            if (! $this->indexExists('tempahan', 'idx_tempahan_conflict_check')) {
                $table->index(
                    ['bilik_id', 'tarikh', 'status', 'masa_mula', 'masa_tamat'],
                    'idx_tempahan_conflict_check'
                );
            }

            // Reporting index tahunan — MySQL & SQLite
            if (! $this->indexExists('tempahan', 'idx_tempahan_laporan_tahunan')) {
                $table->index(
                    ['tarikh', 'status', 'bilik_id', 'kategori'],
                    'idx_tempahan_laporan_tahunan'
                );
            }

            // Reporting index per pengguna — MySQL & SQLite
            if (! $this->indexExists('tempahan', 'idx_tempahan_laporan_pengguna')) {
                $table->index(
                    ['user_id', 'tarikh', 'status'],
                    'idx_tempahan_laporan_pengguna'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            foreach ([
                'idx_tempahan_conflict_check',
                'idx_tempahan_laporan_tahunan',
                'idx_tempahan_laporan_pengguna',
            ] as $idx) {
                if ($this->indexExists('tempahan', $idx)) {
                    $table->dropIndex($idx);
                }
            }
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        if (DB::getDriverName() === 'mysql') {
            return (bool) DB::select(
                "SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]
            );
        }

        // SQLite
        return collect(DB::select("PRAGMA index_list({$table})"))
            ->contains('name', $name);
    }
};
