<?php

/**
 * iBook — Database Audit Fix #5
 * Tambah trigger untuk cegah double booking di peringkat pangkalan data.
 *
 * LAPISAN PERTAHANAN:
 *   Lapisan 1 (App): lockForUpdate() dalam DB::transaction — cegah race condition
 *   Lapisan 2 (DB):  Trigger ini — jaring keselamatan jika lapisan 1 terlepas
 *
 * TRIGGER YANG DICIPTA:
 *   trg_tempahan_no_conflict_insert — BEFORE INSERT
 *   trg_tempahan_no_conflict_update — BEFORE UPDATE
 *
 * SKOP: Hanya untuk MySQL/MariaDB. SQLite (ujian) tidak memerlukan trigger
 *       kerana lockForUpdate() dalam ujian menggunakan SQLite WAL mode.
 *
 * LOGIK: Jika (bilik_id + tarikh + sesi) sudah ada tempahan yang tidak ditolak,
 *        SIGNAL SQLSTATE '45000' dengan mesej yang boleh ditangkap oleh app.
 *
 * NOTA PENTING untuk InfinityFree:
 *   InfinityFree shared hosting TIDAK membenarkan CREATE TRIGGER.
 *   Trigger ini hanya berkesan pada persekitaran lokal dan VPS/dedicated.
 *   Untuk production InfinityFree, keselamatan bergantung pada Lapisan 1 sahaja.
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

        // ── Trigger 1: BEFORE INSERT ───────────────────────────────────────
        DB::unprepared('
            CREATE TRIGGER `trg_tempahan_no_conflict_insert`
            BEFORE INSERT ON `tempahan`
            FOR EACH ROW
            BEGIN
                DECLARE konflik_count INT DEFAULT 0;

                IF NEW.`status` != \'ditolak\' THEN
                    SELECT COUNT(*) INTO konflik_count
                    FROM `tempahan`
                    WHERE `bilik_id` = NEW.`bilik_id`
                      AND `tarikh`   = NEW.`tarikh`
                      AND `sesi`     = NEW.`sesi`
                      AND `status`  != \'ditolak\';

                    IF konflik_count > 0 THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'KONFLIK_SLOT: Bilik telah ditempah untuk sesi dan tarikh ini.\';
                    END IF;
                END IF;
            END
        ');

        // ── Trigger 2: BEFORE UPDATE ───────────────────────────────────────
        DB::unprepared('
            CREATE TRIGGER `trg_tempahan_no_conflict_update`
            BEFORE UPDATE ON `tempahan`
            FOR EACH ROW
            BEGIN
                DECLARE konflik_count INT DEFAULT 0;

                IF NEW.`status` != \'ditolak\' THEN
                    SELECT COUNT(*) INTO konflik_count
                    FROM `tempahan`
                    WHERE `bilik_id` = NEW.`bilik_id`
                      AND `tarikh`   = NEW.`tarikh`
                      AND `sesi`     = NEW.`sesi`
                      AND `status`  != \'ditolak\'
                      AND `id`      != NEW.`id`;

                    IF konflik_count > 0 THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'KONFLIK_SLOT: Bilik telah ditempah untuk sesi dan tarikh ini.\';
                    END IF;
                END IF;
            END
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS `trg_tempahan_no_conflict_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_tempahan_no_conflict_update`');
    }
};
