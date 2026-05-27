<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $table = 'backup_log';

    protected $fillable = [
        'nama_fail',
        'saiz_bytes',
        'jenis',
        'dibuat_oleh',
    ];

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /** Saiz fail dalam format mesra manusia */
    public function getSaizFormatAttribute(): string
    {
        $b = $this->saiz_bytes;
        if ($b < 1024)      return $b . ' B';
        if ($b < 1048576)   return round($b / 1024, 1) . ' KB';
        return round($b / 1048576, 2) . ' MB';
    }

    /** Label jenis backup */
    public function getLabelJenisAttribute(): string
    {
        return match ($this->jenis) {
            'mingguan' => 'Mingguan',
            'bulanan'  => 'Bulanan',
            default    => 'Segera',
        };
    }
}
