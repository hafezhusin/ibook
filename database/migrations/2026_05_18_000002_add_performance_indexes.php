<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->index(['bilik_id', 'tarikh', 'sesi', 'status'], 'idx_tempahan_bilik_tarikh_sesi_status');
            $table->index(['user_id', 'tarikh', 'status'], 'idx_tempahan_user_tarikh_status');
            $table->index(['status', 'tarikh'], 'idx_tempahan_status_tarikh');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('jabatan', 'idx_users_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('tempahan', function (Blueprint $table) {
            $table->dropIndex('idx_tempahan_bilik_tarikh_sesi_status');
            $table->dropIndex('idx_tempahan_user_tarikh_status');
            $table->dropIndex('idx_tempahan_status_tarikh');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_jabatan');
        });
    }
};
