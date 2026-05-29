<?php

/**
 * iBook — Database Audit Fix #2
 * Betulkan schema bilik_mesyuarat yang salah (data diimport dari sumber lain).
 *
 * Isu dijumpai semasa audit:
 *   1. id — tiada AUTO_INCREMENT (Eloquent INSERT akan fail!)
 *   2. Collation berbeza: utf8mb4_0900_ai_ci vs utf8mb4_unicode_ci (seluruh sistem)
 *   3. status — varchar(50) bukan ENUM (tiada DB-level constraint)
 *   4. gambar — varchar(50) terlalu pendek (nama fail boleh melebihi 50 aksara)
 *   5. lokasi — varchar(50) terlalu pendek untuk nama lokasi penuh
 *   6. kemudahan — longtext (utf8mb4_bin) bukan JSON type
 *   7. created_at / updated_at — datetime bukan timestamp
 *   8. Tiada index pada (status, deleted_at) — digunakan setiap halaman
 *
 * SELAMAT: 13 baris data wujud, semua perubahan tidak membuang data.
 *          AUTO_INCREMENT dimulakan dari 18 (max id = 17).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (ujian) tidak sokong sebahagian ALTER ini — skip
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Disable FK checks sementara — diperlukan kerana bilik_mesyuarat.id
        // dirujuk oleh FK tempahan_berulang.fk_tb_bilik.
        // MySQL tidak membenarkan MODIFY COLUMN pada kolum yang dirujuk FK.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // ── Langkah 1: Tukar collation seluruh jadual ─────────────────────
            // Ini akan menukar semua kolum VARCHAR/TEXT ke utf8mb4_unicode_ci
            // selaras dengan seluruh database ibook
            DB::statement('ALTER TABLE `bilik_mesyuarat` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // ── Langkah 2: Tambah AUTO_INCREMENT pada id ───────────────────────
            // id sedia ada: bigint unsigned NOT NULL (tiada AUTO_INCREMENT)
            // max id = 17, AUTO_INCREMENT dimulakan dari 18
            DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = 18');

        // ── Langkah 3: Betulkan kolum status → ENUM ───────────────────────
        // Semua nilai sedia ada adalah 'aktif' — selamat ditukar ke ENUM
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `status` ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif' COLLATE utf8mb4_unicode_ci");

        // ── Langkah 4: Kembangkan kolum gambar → varchar(255) ─────────────
        // varchar(50) terlalu pendek untuk nama fail gambar
        // Semua nilai sedia ada: kosong (length=0) — tiada truncation risk
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `gambar` VARCHAR(255) NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci");

        // ── Langkah 5: Kembangkan kolum lokasi → varchar(255) ─────────────
        // varchar(50) terlalu pendek; max sedia ada hanya 6 aksara — selamat
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `lokasi` VARCHAR(255) NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci");

        // ── Langkah 6: Tukar kemudahan → JSON type ─────────────────────────
        // longtext (utf8mb4_bin) → JSON native
        // Semua nilai sedia ada adalah JSON array yang sah atau NULL
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `kemudahan` JSON NULL');

        // ── Langkah 7: Tukar created_at / updated_at → TIMESTAMP ──────────
        // datetime → timestamp (konsisten dengan seluruh database)
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL');
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL');

            // ── Langkah 8: Tambah index (status, deleted_at) ──────────────────
            // Digunakan hampir setiap query: WHERE status='aktif' AND deleted_at IS NULL
            Schema::table('bilik_mesyuarat', function (Blueprint $table) {
                $table->index(['status', 'deleted_at'], 'idx_bilik_status_deleted');
            });

        } finally {
            // Sentiasa pulihkan FK checks walaupun ada exception
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->dropIndex('idx_bilik_status_deleted');
        });

        // Pulihkan jenis kolum asal (untuk rollback testing — tidak diguna di production)
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `status` VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `gambar` VARCHAR(50) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `lokasi` VARCHAR(50) NOT NULL DEFAULT ''");
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `kemudahan` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL');
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `created_at` DATETIME NOT NULL');
        DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `updated_at` DATETIME NOT NULL');
        // AUTO_INCREMENT dan collation tidak dibalikkan — terlalu berisiko
    }
};
