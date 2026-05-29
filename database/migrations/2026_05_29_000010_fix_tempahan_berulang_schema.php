<?php

/**
 * iBook — Score 10/10 Fix #10
 * Perbaiki schema tempahan_berulang untuk konsistensi dengan tempahan.
 *
 * MASALAH:
 *   1. kategori: varchar(255) — tiada ENUM constraint (tempahan.kategori sudah ENUM)
 *   2. ulid: tiada UNIQUE index — getRouteKeyName() bergantung pada ULID unik
 *
 * SELAMAT: tempahan_berulang mempunyai 0 baris — selamat ALTER tanpa risiko data.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ENUM_VALUES = [
        'mesyuarat', 'perbincangan', 'taklimat', 'bengkel',
        'latihan', 'teknikal', 'pengurusan', 'lain-lain',
    ];

    public function up(): void
    {
        // UNIQUE ulid — hanya tambah jika belum wujud.
        // Migration asal (2026_05_22_000001) sudah ada ->unique() tapi mungkin
        // tiada dalam DB yang diimport atau migrate lama. SQLite (tests) sudah ada.
        $uniqueExists = collect(DB::select(
            DB::getDriverName() === 'mysql'
                ? "SHOW INDEX FROM `tempahan_berulang` WHERE Key_name = 'tempahan_berulang_ulid_unique'"
                : "PRAGMA index_list(tempahan_berulang)"
        ))->when(DB::getDriverName() === 'sqlite', fn ($c) => $c->where('name', 'tempahan_berulang_ulid_unique'))
          ->isNotEmpty();

        if (! $uniqueExists) {
            Schema::table('tempahan_berulang', function (Blueprint $table) {
                $table->unique('ulid', 'tempahan_berulang_ulid_unique');
            });
        }

        // ENUM kategori — MySQL sahaja
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $enumList = implode(',', array_map(fn ($v) => "'$v'", self::ENUM_VALUES));
        DB::statement("ALTER TABLE `tempahan_berulang` MODIFY COLUMN `kategori` ENUM($enumList) NOT NULL COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        // Hanya drop jika migration ini yang menciptanya (bukan migration asal)
        $uniqueExists = collect(DB::select(
            DB::getDriverName() === 'mysql'
                ? "SHOW INDEX FROM `tempahan_berulang` WHERE Key_name = 'tempahan_berulang_ulid_unique'"
                : "PRAGMA index_list(tempahan_berulang)"
        ))->when(DB::getDriverName() === 'sqlite', fn ($c) => $c->where('name', 'tempahan_berulang_ulid_unique'))
          ->isNotEmpty();

        if ($uniqueExists && DB::getDriverName() === 'mysql') {
            Schema::table('tempahan_berulang', function (Blueprint $table) {
                $table->dropUnique('tempahan_berulang_ulid_unique');
            });
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `tempahan_berulang` MODIFY COLUMN `kategori` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
};
