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

        // Tukar enum status kepada dua pilihan sahaja.
        DB::statement("ALTER TABLE tempahan MODIFY status ENUM('diluluskan','ditolak') NOT NULL DEFAULT 'diluluskan'");

        // Index khusus untuk sorting senarai tempahan.
        Schema::table('tempahan', function (Blueprint $table) {
            $table->index(['tarikh', 'masa_mula'], 'idx_tempahan_tarikh_masa_mula');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tempahan MODIFY status ENUM('menunggu','diluluskan','ditolak') NOT NULL DEFAULT 'menunggu'");

        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropIndex('idx_tempahan_tarikh_masa_mula');
        });
    }
};
