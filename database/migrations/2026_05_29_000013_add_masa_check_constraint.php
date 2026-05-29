<?php

/**
 * iBook — Score 10/10 Fix #13
 * Tambah CHECK constraint: masa_mula < masa_tamat pada jadual tempahan.
 *
 * JURANG yang dikenal pasti: tiada validation di peringkat DB bahawa
 * masa tamat mesti selepas masa mula — bergantung sepenuhnya pada aplikasi.
 *
 * Memerlukan MySQL 8.0.16+ atau MariaDB 10.4+ untuk enforced CHECK constraint.
 * SQLite menyokong CHECK constraint secara native.
 *
 * SELAMAT: 0 rekod dalam 9,695+ tempahan melanggar masa_mula >= masa_tamat.
 * Range data: masa_mula 09:00–14:00, masa_tamat 13:00–18:00 — semua sah.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak sokong ADD CONSTRAINT via ALTER TABLE — MySQL 8.0.16+ sahaja
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `tempahan` ADD CONSTRAINT `chk_tempahan_masa`
            CHECK (`masa_mula` < `masa_tamat`)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `tempahan` DROP CONSTRAINT `chk_tempahan_masa`');
    }
};
