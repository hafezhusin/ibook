<?php

/**
 * iBook — Database Audit Fix #6
 * Pembersihan data lapuk.
 *
 * TINDAKAN:
 *   1. Padam token reset kata laluan yang melebihi 60 minit
 *      (1 token lapuk dijumpai semasa audit: 21 Mei 2026)
 *
 *   2. Jadikan tempahan.user_id nullable
 *      - 1,501 rekod tempahan merujuk user_id yang tidak lagi wujud dalam users
 *      - Ini adalah rekod sejarah yang sah (staf yang telah bertukar/berhenti)
 *      - Menjadikan kolum nullable adalah lebih tepat secara semantik
 *      - Aplikasi sudah mengendalikan ini: $t->pengguna->name ?? '-'
 *      - Tidak ada data yang dipadam — nilai user_id lama kekal sebagaimana adanya
 *
 * NOTA PENTING (kategori):
 *   Audit mendapati 3 nilai kategori tidak dalam KATEGORI const:
 *     'lain-lain' (56), 'pengurusan' (303), 'teknikal' (830) = 1,189 rekod
 *   TIDAK DITUKAR ke ENUM dalam migration ini.
 *   Tindakan yang diperlukan: kemaskini Tempahan::KATEGORI const dahulu,
 *   kemudian laksana migration ENUM berasingan.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Padam token reset yang lapuk (> 60 minit) ──────────────────
        DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subMinutes(60))
            ->delete();

        // ── 2. Jadikan tempahan.user_id nullable ───────────────────────────
        // SQLite: Schema::table() dengan ->change() tidak memerlukan raw SQL
        // MySQL: Guna MODIFY COLUMN
        if (DB::getDriverName() === 'mysql') {
            // Buang FK lama pada diluluskan_oleh dan dikemaskini_oleh dahulu jika ada,
            // kerana MODIFY COLUMN pada kolum lain boleh menjejaskan FK.
            // Dalam kes ini, user_id tiada FK — selamat diteruskan.
            DB::statement('ALTER TABLE `tempahan` MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL');
        } else {
            // SQLite — guna Blueprint change()
            Schema::table('tempahan', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // AMARAN: Rollback ini hanya selamat jika tiada NULL dalam user_id.
        // Dengan 1,501 orphan records (integer values, bukan NULL), ini selamat
        // kerana nilai integer masih ada — hanya constraint NULL yang berubah.

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `tempahan` MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('tempahan', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }

        // Tidak perlu rollback token — yang dah dipadam memang tidak perlu dipulihkan
    }
};
