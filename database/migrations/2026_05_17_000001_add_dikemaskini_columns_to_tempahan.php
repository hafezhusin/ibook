<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->unsignedBigInteger('dikemaskini_oleh')->nullable()->after('diluluskan_pada');
            $table->timestamp('dikemaskini_pada')->nullable()->after('dikemaskini_oleh');

            $table->foreign('dikemaskini_oleh')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropForeign(['dikemaskini_oleh']);
            $table->dropColumn(['dikemaskini_oleh', 'dikemaskini_pada']);
        });
    }
};
