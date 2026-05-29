<?php

/**
 * iBook — Database Audit Fix #7
 * Tambah ENUM constraint pada tempahan.kategori.
 *
 * SEBELUM: varchar — tiada DB-level constraint, menerima sebarang nilai
 * SELEPAS: ENUM dengan 8 nilai yang sah
 *
 * Nilai semasa dalam DB (sebelum migration):
 *   mesyuarat (5470), perbincangan (2504), teknikal (830),
 *   latihan (533), pengurusan (303), lain-lain (56)
 *
 * Nilai dalam Tempahan::KATEGORI const (termasuk yang tiada data):
 *   + taklimat, bengkel (valid untuk tempahan baru)
 *
 * Prasyarat: Tempahan::KATEGORI telah dikemaskini untuk include
 *   teknikal, pengurusan, lain-lain sebelum migration ini.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ENUM_VALUES = [
        'mesyuarat', 'perbincangan', 'taklimat', 'bengkel',
        'latihan', 'teknikal', 'pengurusan', 'lain-lain',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $enumList = implode(',', array_map(fn ($v) => "'$v'", self::ENUM_VALUES));

        DB::statement("ALTER TABLE `tempahan` MODIFY COLUMN `kategori` ENUM($enumList) NOT NULL COLLATE utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `tempahan` MODIFY COLUMN `kategori` VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci");
    }
};
