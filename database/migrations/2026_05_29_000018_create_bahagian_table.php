<?php

/**
 * iBook — Score 100/100 Multi-Tenant Foundation #18
 * Cipta jadual `bahagian` — asas untuk sistem multi-bahagian.
 *
 * Rekod BPTM disisip secara automatik sebagai bahagian pertama (id=1)
 * supaya bilik dan pengguna sedia ada dapat dipautkan tanpa kerja tambahan.
 *
 * cross_booking_aktif = 0 secara lalai — tidak ada staf luar yang
 * nampak bilik bahagian ini sehingga pentadbir sistem mengaktifkannya.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahagian', function (Blueprint $table) {
            $table->id();
            $table->string('kod', 20)->unique()->comment('Singkatan unik — BPTM, JPA, MAMPU');
            $table->string('nama', 150)->comment('Nama penuh bahagian/jabatan');
            $table->text('lokasi')->nullable()->comment('Aras, blok, bangunan');
            $table->string('telefon', 20)->nullable();
            $table->string('emel', 100)->nullable()->comment('CC notifikasi kelulusan cross-booking');
            $table->boolean('aktif')->default(true)->comment('Bahagian beroperasi dalam sistem');
            $table->boolean('cross_booking_aktif')->default(false)
                  ->comment('Bilik bahagian ini boleh dibooking oleh staf luar');
            $table->timestamps();
        });

        // Sisip BPTM sebagai bahagian pertama — semua bilik & pengguna sedia ada akan dipautkan ke sini
        DB::table('bahagian')->insert([
            'kod'                  => 'BPTM',
            'nama'                 => 'Bahagian Pengurusan Teknologi Maklumat',
            'lokasi'               => null,
            'telefon'              => null,
            'emel'                 => null,
            'aktif'                => 1,
            'cross_booking_aktif'  => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('bahagian');
    }
};
