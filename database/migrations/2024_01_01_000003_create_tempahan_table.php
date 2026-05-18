<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempahan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mesyuarat');
            $table->date('tarikh');
            $table->enum('sesi', ['pagi', 'petang']);
            $table->time('masa_mula');
            $table->time('masa_tamat');
            $table->foreignId('bilik_id')->constrained('bilik_mesyuarat')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('bilangan_peserta');
            $table->string('kategori');
            $table->string('nama_pengerusi');
            $table->text('tujuan')->nullable();
            $table->enum('status', ['diluluskan', 'ditolak'])->default('diluluskan');
            $table->text('catatan_penolakan')->nullable();
            $table->foreignId('diluluskan_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('diluluskan_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tempahan');
    }
};
