<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User|null $pengguna  Pengguna yang melakukan tindakan (null = tindakan sistem)
 */
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false; // Guna 'dicipta_pada' sahaja

    protected $fillable = [
        'pengguna_id',
        'tindakan',
        'model_jenis',
        'model_id',
        'penerangan',
        'butiran',
        'ip_address',
        'prev_hash',
        'record_hash',
        'dicipta_pada',
    ];

    protected $casts = [
        'butiran'      => 'array',
        'dicipta_pada' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
