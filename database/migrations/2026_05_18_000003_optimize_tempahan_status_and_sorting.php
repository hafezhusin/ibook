<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalisasi status lama kepada domain auto-approve.
        DB::table('tempahan')
            ->where('status', 'menunggu')
            ->update(['status' => 'diluluskan']);

        // ENUM MODIFY hanya relevan untuk MySQL/MariaDB.
        // SQLite (digunakan semasa testing) tidak sokong ALTER MODIFY.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tempahan MODIFY status ENUM('diluluskan','ditolak') NOT NULL DEFAULT 'diluluskan'");
        }

        // Index khusus untuk sorting senarai tempahan.
        // SQLite sokong CREATE INDEX, jadi ini selamat untuk kedua-dua driver.
        if (! $this->indexExists('tempahan', 'idx_tempahan_tarikh_masa_mula')) {
            Schema::table('tempahan', function (Blueprint $table) {
                $table->index(['tarikh', 'masa_mula'], 'idx_tempahan_tarikh_masa_mula');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tempahan MODIFY status ENUM('menunggu','diluluskan','ditolak') NOT NULL DEFAULT 'menunggu'");
        }

        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropIndex('idx_tempahan_tarikh_masa_mula');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("PRAGMA index_list({$table})");
            foreach ($indexes as $idx) {
                if ($idx->name === $index) {
                    return true;
                }
            }

            return false;
        } catch (Throwable) {
            return false; // MySQL — biarkan schema builder uruskan
        }
    }
};
