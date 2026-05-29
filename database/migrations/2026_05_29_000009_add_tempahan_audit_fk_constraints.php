<?php

/**
 * iBook — Score 10/10 Fix #9
 * Tambah FK constraint pada tempahan.diluluskan_oleh dan tempahan.dikemaskini_oleh.
 *
 * SEBELUM: Index *_foreign wujud (nama Laravel) tapi FK constraint sebenar tiada.
 *          Ini berlaku kerana migration asal dibuat untuk SQLite compatibility.
 * SELEPAS: FK sebenar ke users(id) ON DELETE SET NULL — konsisten dengan semua FK lain.
 *
 * SELAMAT:
 *   diluluskan_oleh: 0 orphan records (semua non-null merujuk user sah)
 *   dikemaskini_oleh: 0 non-null rows
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

        Schema::table('tempahan', function (Blueprint $table) {
            // Guna nama FK eksplisit supaya mudah di-drop dalam down()
            $table->foreign('diluluskan_oleh', 'fk_tempahan_diluluskan_oleh')
                ->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('dikemaskini_oleh', 'fk_tempahan_dikemaskini_oleh')
                ->references('id')->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropForeign('fk_tempahan_diluluskan_oleh');
            $table->dropForeign('fk_tempahan_dikemaskini_oleh');
        });
    }
};
