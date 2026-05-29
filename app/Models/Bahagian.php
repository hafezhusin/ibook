<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $kod
 * @property string $nama
 * @property string|null $lokasi
 * @property string|null $telefon
 * @property string|null $emel
 * @property bool   $aktif
 * @property bool   $cross_booking_aktif
 */
class Bahagian extends Model
{
    protected $table = 'bahagian';

    protected $fillable = [
        'kod',
        'nama',
        'lokasi',
        'telefon',
        'emel',
        'aktif',
        'cross_booking_aktif',
    ];

    protected $casts = [
        'aktif'               => 'boolean',
        'cross_booking_aktif' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function bilik(): HasMany
    {
        return $this->hasMany(BilikMesyuarat::class, 'bahagian_id');
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class, 'bahagian_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->aktif === true;
    }

    public function isCrossBookingAktif(): bool
    {
        return $this->cross_booking_aktif === true;
    }

    /**
     * Bilangan bilik aktif yang dimiliki bahagian ini.
     */
    public function bilanganBilikAktif(): int
    {
        return $this->bilik()->where('status', 'aktif')->count();
    }
}
