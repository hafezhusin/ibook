<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->unique()->after('id');
        });

        // Backfill existing records
        DB::table('bilik_mesyuarat')->orderBy('id')->each(function ($bilik) {
            DB::table('bilik_mesyuarat')
                ->where('id', $bilik->id)
                ->update(['ulid' => (string) Str::ulid()]);
        });

        // Make NOT NULL after backfill
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bilik_mesyuarat', function (Blueprint $table) {
            $table->dropColumn('ulid');
        });
    }
};
