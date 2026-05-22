<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->foreignId('tempahan_berulang_id')
                ->nullable()
                ->after('ulid')
                ->comment('FK ke kumpulan ulangan — NULL jika bukan tempahan berulang')
                ->constrained('tempahan_berulang')
                ->nullOnDelete();
            $table->index('tempahan_berulang_id', 'idx_tempahan_berulang_id');
        });
    }

    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropForeign(['tempahan_berulang_id']);
            $table->dropIndex('idx_tempahan_berulang_id');
            $table->dropColumn('tempahan_berulang_id');
        });
    }
};
