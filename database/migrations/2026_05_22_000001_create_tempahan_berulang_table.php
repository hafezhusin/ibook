<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempahan_berulang', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->enum('jenis', ['mingguan', 'bulanan']);
            $table->tinyInteger('setiap_n')->unsigned()->default(1)->comment('Setiap N minggu atau N bulan');
            $table->json('hari_dalam_minggu')->nullable()->comment('Array 0-6 (Ahad=0) — untuk jenis mingguan sahaja');
            $table->date('tarikh_mula');
            $table->date('tarikh_tamat');
            $table->json('sesi')->comment('Array: ["pagi"] atau ["pagi","petang"]');
            $table->foreignId('bilik_id')->constrained('bilik_mesyuarat')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_mesyuarat');
            $table->integer('bilangan_peserta');
            $table->string('kategori');
            $table->string('nama_pengerusi');
            $table->text('tujuan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tempahan_berulang');
    }
};
