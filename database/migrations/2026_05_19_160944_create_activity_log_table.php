<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengguna_id')->nullable(); // null = tindakan sistem
            $table->string('tindakan', 100);                       // cth: 'buat_tempahan', 'eksport_pdf'
            $table->string('model_jenis', 100)->nullable();        // cth: 'Tempahan', 'User'
            $table->unsignedBigInteger('model_id')->nullable();    // ID rekod yang terkesan
            $table->string('penerangan')->nullable();              // Teks ringkas boleh baca manusia
            $table->json('butiran')->nullable();                   // Data tambahan (json)
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('dicipta_pada')->useCurrent();

            $table->index('pengguna_id');
            $table->index(['model_jenis', 'model_id']);
            $table->index('tindakan');
            $table->index('dicipta_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
