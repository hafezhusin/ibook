<?php

/**
 * iBook — Database Audit Fix #1
 * Betulkan FK CASCADE pada tempahan_berulang.
 *
 * SEBELUM: bilik_id & user_id menggunakan ON DELETE CASCADE
 *          (padam bilik/user akan padam semua kumpulan berulang)
 * SELEPAS: bilik_id → RESTRICT | user_id → SET NULL (kolum dijadikan nullable)
 *
 * SELAMAT: tempahan_berulang mempunyai 0 baris — tiada risiko kehilangan data.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (ujian) tidak sokong DROP FOREIGN KEY — skip
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('tempahan_berulang', function (Blueprint $table) {
            // 1. Buang FK lama (CASCADE)
            $table->dropForeign('fk_tb_bilik');
            $table->dropForeign('fk_tb_user');

            // 2. Jadikan user_id nullable dahulu — supaya SET NULL boleh berfungsi
            //    dan logik bisnes lebih tepat (user boleh dipadam tanpa musnahkan
            //    kumpulan berulang yang menjadi rekod sejarah)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 3. Tambah FK baharu dengan dasar betul
            //    bilik_id → RESTRICT: tidak boleh padam bilik jika ada kumpulan berulang aktif
            $table->foreign('bilik_id', 'fk_tb_bilik')
                ->references('id')->on('bilik_mesyuarat')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            //    user_id → SET NULL: padam user hanya nullkan rujukan, kumpulan berulang kekal
            $table->foreign('user_id', 'fk_tb_user')
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

        Schema::table('tempahan_berulang', function (Blueprint $table) {
            $table->dropForeign('fk_tb_bilik');
            $table->dropForeign('fk_tb_user');

            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Pulihkan CASCADE asal (untuk rollback sahaja)
            $table->foreign('bilik_id', 'fk_tb_bilik')
                ->references('id')->on('bilik_mesyuarat')
                ->onDelete('cascade');

            $table->foreign('user_id', 'fk_tb_user')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
};
