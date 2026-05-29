<?php

/**
 * iBook — Database Audit Fix #8
 * Tukar bilik_mesyuarat.dikemaskini_oleh dari varchar(255) ke bigint FK.
 *
 * SEBELUM: varchar(255) NULL — tidak konsisten dengan jadual lain yang guna bigint FK
 * SELEPAS: bigint unsigned NULL + FK → users(id) ON DELETE SET NULL
 *
 * SELAMAT: Semua 13 rekod bilik_mesyuarat mempunyai dikemaskini_oleh = NULL.
 *          Tiada data string untuk di-migrate. Boleh ALTER terus.
 *
 * Nota: tempahan.dikemaskini_oleh sudah bigint unsigned — hanya bilik_mesyuarat
 *       yang perlu diperbetulkan.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::statement('ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `dikemaskini_oleh` BIGINT UNSIGNED NULL DEFAULT NULL');

            Schema::table('bilik_mesyuarat', function (Blueprint $table) {
                $table->foreign('dikemaskini_oleh', 'fk_bm_dikemaskini_oleh')
                    ->references('id')->on('users')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->dropForeign('fk_bm_dikemaskini_oleh');
        });

        DB::statement("ALTER TABLE `bilik_mesyuarat` MODIFY COLUMN `dikemaskini_oleh` VARCHAR(255) NULL DEFAULT NULL COLLATE utf8mb4_unicode_ci");
    }
};
