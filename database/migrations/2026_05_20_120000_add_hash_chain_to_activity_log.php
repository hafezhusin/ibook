<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Hash rekod sebelumnya — membentuk rantai SHA-256
            $table->string('prev_hash', 64)->nullable()->after('ip_address');
            // Hash rekod ini sendiri (SHA-256 bagi semua medan kanonik)
            $table->string('record_hash', 64)->nullable()->after('prev_hash');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['prev_hash', 'record_hash']);
        });
    }
};
