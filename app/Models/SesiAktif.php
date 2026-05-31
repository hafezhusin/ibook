<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiAktif extends Model
{
    protected $table      = 'sesi_aktif';
    public    $timestamps = false;

    protected $fillable = [
        'pengguna_id',
        'session_id',
        'ip_address',
        'user_agent',
        'kaedah',
        'log_masuk_pada',
        'aktiviti_terakhir',
    ];

    protected $casts = [
        'log_masuk_pada'    => 'datetime',
        'aktiviti_terakhir' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    /**
     * Padam sesi lapuk melebihi hayat sesi sistem.
     */
    public static function bersihStale(int $menitHayat = 60): int
    {
        return static::where('aktiviti_terakhir', '<', now()->subMinutes($menitHayat))->delete();
    }

    /**
     * Detect nama browser dari User-Agent string.
     */
    public function getBrowserAttribute(): string
    {
        $ua = $this->user_agent ?? '';

        return match (true) {
            str_contains($ua, 'Edg/')    => 'Edge',
            str_contains($ua, 'OPR/')    => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && str_contains($ua, 'Version/') => 'Safari',
            str_contains($ua, 'MSIE') || str_contains($ua, 'Trident/') => 'Internet Explorer',
            str_contains($ua, 'curl/')   => 'cURL',
            $ua === ''                   => '—',
            default                      => 'Lain-lain',
        };
    }

    /**
     * Detect OS dari User-Agent string.
     */
    public function getOsAttribute(): string
    {
        $ua = $this->user_agent ?? '';

        return match (true) {
            str_contains($ua, 'Windows NT') => 'Windows',
            str_contains($ua, 'Macintosh')  => 'macOS',
            str_contains($ua, 'Linux')      => 'Linux',
            str_contains($ua, 'Android')    => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            default => '—',
        };
    }
}
