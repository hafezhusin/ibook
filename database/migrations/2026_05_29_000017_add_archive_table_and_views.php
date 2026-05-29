<?php

/**
 * iBook — Score 100/100 Fix #17
 * Tambah jadual archive + view sargable untuk skalabiliti dan kesiapan masa depan.
 *
 * KOMPONEN:
 *
 * 1. tempahan_archive
 *    Jadual berasingan dengan struktur sama seperti tempahan, tanpa FK aktif.
 *    Tujuan: pindahkan rekod lama (≥3 tahun) secara berkala supaya jadual utama
 *    kekal ringan. Bila data berkembang ke 50,000+ rekod, ini mengekalkan
 *    prestasi index dan menjamin laporan semasa adalah pantas.
 *    Tiada uq_tempahan_slot_exact — data sejarah boleh ada duplikat sesi lama.
 *
 * 2. vw_tempahan_semasa
 *    View sargable untuk 2 tahun terakhir — menggunakan range predicate betul
 *    (BUKAN YEAR(tarikh) yang menyebabkan full table scan).
 *    Laporan operasi biasa tidak perlu query data 10 tahun lalu.
 *
 * 3. vw_tempahan_2026
 *    View sargable untuk tahun 2026 — contoh pattern penamaan vw_tempahan_YYYY.
 *    Ulangan setiap tahun baru: cipta view tahun baru, padam view 5 tahun lalu.
 *
 * NOTA TEKNIKAL — Mengapa sargable penting:
 *   WHERE YEAR(tarikh) = 2026   → non-sargable → full table scan (index tidak digunakan)
 *   WHERE tarikh >= '2026-01-01'
 *     AND tarikh < '2027-01-01' → sargable     → index range scan (pantas)
 *
 * MySQL/MariaDB sahaja untuk archive table (CREATE TABLE ... LIKE).
 * View boleh dibuat di MySQL — SQLite test suite tidak perlu view ini.
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

        // ── 1. Jadual tempahan_archive ────────────────────────────────────
        $archiveExists = (bool) DB::select(
            "SELECT 1 FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tempahan_archive'"
        );

        if (! $archiveExists) {
            // Salin struktur penuh (kolum, index, generated column) — tanpa FK
            DB::statement('CREATE TABLE `tempahan_archive` LIKE `tempahan`');

            // Buang unique slot constraint — rekod sejarah boleh ada masa yang sama
            $slotIdxExists = (bool) DB::select(
                "SHOW INDEX FROM `tempahan_archive` WHERE Key_name = 'uq_tempahan_slot_exact'"
            );
            if ($slotIdxExists) {
                DB::statement("DROP INDEX `uq_tempahan_slot_exact` ON `tempahan_archive`");
            }

            // Tambah kolum arkib untuk jejak bila rekod dipindahkan
            DB::statement(
                "ALTER TABLE `tempahan_archive`
                 ADD COLUMN `diarkib_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 COMMENT 'Masa rekod dipindahkan ke archive'"
            );
        }

        // ── 2. View tempahan semasa (2 tahun terakhir) — sargable ─────────
        DB::statement("
            CREATE OR REPLACE VIEW `vw_tempahan_semasa` AS
            SELECT * FROM `tempahan`
            WHERE `tarikh` >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 YEAR), '%Y-01-01')
        ");

        // ── 3. View tempahan 2026 — sargable range predicate ─────────────
        DB::statement("
            CREATE OR REPLACE VIEW `vw_tempahan_2026` AS
            SELECT * FROM `tempahan`
            WHERE `tarikh` >= '2026-01-01' AND `tarikh` < '2027-01-01'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS `vw_tempahan_2026`');
        DB::statement('DROP VIEW IF EXISTS `vw_tempahan_semasa`');
        DB::statement('DROP TABLE IF EXISTS `tempahan_archive`');
    }
};
