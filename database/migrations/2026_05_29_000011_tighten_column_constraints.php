<?php

/**
 * iBook — Score 10/10 Fix #11
 * Ketatkan constraint kolum yang masih longgar.
 *
 * PERUBAHAN:
 *   1. bilik_mesyuarat.kapasiti: int → SMALLINT UNSIGNED
 *      - Bilik mesyuarat tidak mungkin ada kapasiti > 65,535
 *      - Range sebenar: 10–60 (dari audit)
 *      - UNSIGNED: kapasiti negatif tidak bermakna
 *
 *   2. activity_log.record_hash: NULL → NOT NULL
 *      - AuditLogger sentiasa mengira hash sebelum INSERT (hash('sha256', $kanonikal))
 *      - NULL record_hash tidak pernah berlaku dalam aliran normal
 *      - NOT NULL menguatkan integriti rantai hash — mustahil rekod "kosong"
 *
 *   3. backup_log.checksum: NULL → NOT NULL DEFAULT ''
 *      - Checksum dikira semasa proses backup — tiada sebab untuk NULL
 *      - DEFAULT '' membolehkan INSERT tanpa checksum jika backup gagal separuh jalan
 *
 * SELAMAT:
 *   kapasiti: min=10, max=60 — jauh di bawah 65,535
 *   record_hash: 0 NULL dalam 31 rekod sedia ada
 *   checksum: 0 rekod dalam backup_log
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

        // 1. kapasiti: int → SMALLINT UNSIGNED
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `kapasiti` SMALLINT UNSIGNED NOT NULL');

        // 2. record_hash: NULL → NOT NULL
        DB::statement('ALTER TABLE `activity_log` MODIFY COLUMN `record_hash` VARCHAR(64) NOT NULL');

        // 3. checksum: NULL → NOT NULL DEFAULT ''
        DB::statement("ALTER TABLE `backup_log` MODIFY COLUMN `checksum` VARCHAR(64) NOT NULL DEFAULT ''");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `kapasiti` INT NOT NULL');
        DB::statement('ALTER TABLE `activity_log` MODIFY COLUMN `record_hash` VARCHAR(64) NULL');
        DB::statement("ALTER TABLE `backup_log` MODIFY COLUMN `checksum` VARCHAR(64) NULL DEFAULT NULL");
    }
};
