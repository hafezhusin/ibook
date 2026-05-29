<?php

/**
 * iBook — Database Audit Fix #4
 * Betulkan jenis kolum bilangan_peserta: int → SMALLINT UNSIGNED
 *
 * MASALAH:
 *   - `int` (signed) membenarkan nilai negatif — logik tidak masuk akal
 *   - 25 rekod dalam tempahan mempunyai bilangan_peserta <= 0 (termasuk 0)
 *   - tempahan_berulang tiada data (0 baris) — selamat ditukar terus
 *
 * TINDAKAN:
 *   1. Betulkan 25 rekod dengan bilangan_peserta <= 0 → set ke 1
 *      (1 peserta = minimum logik; log dalam butiran)
 *   2. Tukar kolum ke SMALLINT UNSIGNED NOT NULL
 *      (max 65,535 peserta — lebih daripada cukup untuk bilik mesyuarat)
 *
 * SELAMAT: MAX bilangan_peserta sedia ada = 184 — jauh di bawah 65,535.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite menggunakan 'integer' sahaja, tidak sokong SMALLINT UNSIGNED persis
        // Tetapi kita tetap betulkan data (langkah 1) untuk kedua-dua driver
        // Langkah 2 (MODIFY COLUMN) hanya untuk MySQL

        // ── Langkah 1: Betulkan data tidak sah ────────────────────────────
        $jumlah = DB::table('tempahan')
            ->where('bilangan_peserta', '<=', 0)
            ->count();

        if ($jumlah > 0) {
            DB::table('tempahan')
                ->where('bilangan_peserta', '<=', 0)
                ->update(['bilangan_peserta' => 1]);
        }

        // ── Langkah 2: Tukar jenis kolum (MySQL sahaja) ───────────────────
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `tempahan` MODIFY COLUMN `bilangan_peserta` SMALLINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `tempahan_berulang` MODIFY COLUMN `bilangan_peserta` SMALLINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `tempahan` MODIFY COLUMN `bilangan_peserta` INT NOT NULL');
        DB::statement('ALTER TABLE `tempahan_berulang` MODIFY COLUMN `bilangan_peserta` INT NOT NULL');
        // Data yang telah dibetulkan (0 → 1) tidak dikembalikan semula — ia tetap 1
    }
};
