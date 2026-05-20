<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tambah lajur ulid ke jadual tempahan (pendekatan aditif — PK integer kekal).
     * ULID digunakan untuk URL-safe public reference, bukan menggantikan PK dalaman.
     */
    public function up(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->unique()->after('id')
                  ->comment('ULID untuk rujukan URL awam — tidak menggantikan PK integer');
        });

        // Isi ULID untuk rekod sedia ada
        \DB::table('tempahan')->orderBy('id')->each(function ($row) {
            \DB::table('tempahan')
                ->where('id', $row->id)
                ->update(['ulid' => (string) Str::ulid()]);
        });

        // Selepas isi semua, jadikan NOT NULL
        Schema::table('tempahan', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable(false)->change();
        });
    }

    /**
     * Rollback: buang lajur ulid sahaja.
     */
    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropColumn('ulid');
        });
    }
};
