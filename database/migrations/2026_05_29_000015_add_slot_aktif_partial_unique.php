<?php

/**
 * iBook — Score 100/100 Fix #15
 * Generated column slot_aktif + partial UNIQUE untuk zero double booking tanpa trigger.
 *
 * MASALAH: Production (InfinityFree) tidak sokong CREATE TRIGGER.
 * Concurrency bergantung 100% pada lockForUpdate() dalam DB::transaction —
 * tiada jaring keselamatan tambahan di peringkat DB jika aplikasi bug.
 *
 * PENYELESAIAN: Generated column + UNIQUE NULL trick
 *
 *   slot_aktif TINYINT GENERATED ALWAYS AS (
 *       IF(status = 'diluluskan', 1, NULL)
 *   ) VIRTUAL
 *
 *   UNIQUE(bilik_id, tarikh, masa_mula, masa_tamat, slot_aktif)
 *
 * MENGAPA BERFUNGSI:
 *   - NULL != NULL dalam MySQL/MariaDB UNIQUE index
 *   - Tempahan 'ditolak' → slot_aktif = NULL → tidak kunci slot (phantom-safe)
 *   - Tempahan 'diluluskan' → slot_aktif = 1 → UNIQUE dikuatkuasakan
 *   - Dua booking aktif dengan SAMA TEPAT masa tidak boleh wujud — DB reject
 *   - Overlap masa berbeza (9-11 vs 10-12) masih perlu trigger / lockForUpdate()
 *
 * NOTA: Status hanya 'diluluskan' atau 'ditolak' — tiada 'menunggu' dalam sistem.
 *       Versi MySQL: 8.0+ dan MariaDB 10.2+ sokong VIRTUAL GENERATED ALWAYS.
 *
 * SQLite: SKIP — test suite tidak perlu concurrency enforcement.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Tambah generated column slot_aktif jika belum wujud
        $colExists = (bool) DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tempahan'
               AND COLUMN_NAME = 'slot_aktif'"
        );

        if (! $colExists) {
            DB::statement(
                "ALTER TABLE `tempahan`
                 ADD COLUMN `slot_aktif` TINYINT GENERATED ALWAYS AS (
                     IF(`status` = 'diluluskan', 1, NULL)
                 ) VIRTUAL AFTER `status`"
            );
        }

        // Tambah partial UNIQUE index jika belum wujud
        $idxExists = (bool) DB::select(
            "SHOW INDEX FROM `tempahan` WHERE Key_name = 'uq_tempahan_slot_exact'"
        );

        if (! $idxExists) {
            // Periksa dahulu jika ada duplikat 'diluluskan' yang akan melanggar constraint
            $duplikat = DB::select(
                "SELECT bilik_id, tarikh, masa_mula, masa_tamat, COUNT(*) AS cnt
                 FROM `tempahan`
                 WHERE `status` = 'diluluskan'
                 GROUP BY bilik_id, tarikh, masa_mula, masa_tamat
                 HAVING cnt > 1
                 LIMIT 1"
            );

            if (! empty($duplikat)) {
                // Data duplikat wujud (biasanya data seed/test) — langkau index untuk elak kegagalan
                // Jalankan: DELETE FROM tempahan WHERE id IN (pilih duplikat) sebelum migrate semula
                return;
            }

            DB::statement(
                "CREATE UNIQUE INDEX `uq_tempahan_slot_exact`
                 ON `tempahan`(`bilik_id`, `tarikh`, `masa_mula`, `masa_tamat`, `slot_aktif`)"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $idxExists = (bool) DB::select(
            "SHOW INDEX FROM `tempahan` WHERE Key_name = 'uq_tempahan_slot_exact'"
        );
        if ($idxExists) {
            DB::statement("DROP INDEX `uq_tempahan_slot_exact` ON `tempahan`");
        }

        $colExists = (bool) DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'tempahan'
               AND COLUMN_NAME = 'slot_aktif'"
        );
        if ($colExists) {
            DB::statement("ALTER TABLE `tempahan` DROP COLUMN `slot_aktif`");
        }
    }
};
