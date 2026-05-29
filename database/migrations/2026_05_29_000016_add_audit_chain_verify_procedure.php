<?php

/**
 * iBook — Score 100/100 Fix #16
 * Stored procedure sp_verify_audit_chain untuk verifikasi integriti hash chain
 * terus di peringkat DB — tanpa perlu masuk ke lapisan aplikasi Laravel.
 *
 * TUJUAN:
 *   AuditLogger mengira record_hash = SHA-256(JSON kanonikal) untuk setiap baris.
 *   prev_hash baris N mestilah sama dengan record_hash baris N-1.
 *   Jika seseorang mengubah data activity_log secara terus (SQL), chain akan patah.
 *
 * CARA GUNA:
 *   CALL sp_verify_audit_chain(@rosak_rantai, @rosak_format);
 *   SELECT @rosak_rantai, @rosak_format;
 *   -- 0, 0 → chain intact
 *   -- > 0  → ada tamper / data rosak
 *
 * VERIFIKASI DUA LAPISAN:
 *   1. Format   : record_hash mestilah tepat 64 aksara hex (SHA-256 signature)
 *   2. Rantai   : prev_hash baris N = record_hash baris N-1 (urutan dicipta_pada, id)
 *                 Guna LAG() window function (MariaDB 10.2+ / MySQL 8.0+)
 *
 * NOTA: Verifikasi hash penuh (recompute SHA-256) dilakukan oleh `php artisan audit:verify`
 *       Procedure ini melengkapi — verifikasi pantas struktur rantai tanpa PHP.
 *
 * MySQL/MariaDB sahaja — SQLite tidak ada stored procedures.
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

        // DROP dahulu jika wujud — supaya idempotent
        DB::unprepared('DROP PROCEDURE IF EXISTS `sp_verify_audit_chain`');

        DB::unprepared("
CREATE PROCEDURE `sp_verify_audit_chain`(
    OUT p_rantai_rosak INT,
    OUT p_format_rosak INT
)
BEGIN
    -- 1. Semak format: setiap record_hash mestilah SHA-256 hex (64 aksara [0-9a-f])
    SELECT COUNT(*) INTO p_format_rosak
    FROM `activity_log`
    WHERE `record_hash` IS NOT NULL
      AND `record_hash` NOT REGEXP '^[0-9a-f]{64}\$';

    -- 2. Semak rantai: prev_hash baris N mestilah = record_hash baris N-1
    --    Guna LAG() window function (MariaDB 10.2+ / MySQL 8.0+)
    --    Urutan sama seperti AuditLogger: latest('dicipta_pada')->latest('id')
    SELECT COUNT(*) INTO p_rantai_rosak
    FROM (
        SELECT
            `id`,
            `prev_hash`,
            LAG(`record_hash`) OVER (
                ORDER BY `dicipta_pada` ASC, `id` ASC
            ) AS `expected_prev_hash`
        FROM `activity_log`
    ) AS `chain_check`
    WHERE `prev_hash` IS NOT NULL
      AND `prev_hash` != `expected_prev_hash`;
END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS `sp_verify_audit_chain`');
    }
};
