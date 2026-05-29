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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property-read Collection<int, Tempahan> $tempahan      Semua tempahan untuk bilik ini
 * @property float|null $peratus_penggunaan Dikira dari query agregat (bukan lajur DB)
 * @property int|null $tempahan_bulan_ini Dikira dari query agregat (bukan lajur DB)
 */
class BilikMesyuarat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bilik_mesyuarat';

    protected $fillable = [
        'bahagian_id',
        'nama',
        'kapasiti',
        'kemudahan',
        'status',
        'gambar',
        'lokasi',
        'ulid',
        'dikemaskini_oleh',
        'dikemaskini_pada',
    ];

    protected $casts = [
        'kemudahan' => 'array',
        'kapasiti' => 'integer',
        'dikemaskini_pada' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (BilikMesyuarat $bilik) {
            if (empty($bilik->ulid)) {
                $bilik->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function tempahan(): HasMany
    {
        return $this->hasMany(Tempahan::class, 'bilik_id');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikemaskini_oleh');
    }

    public function bahagian(): BelongsTo
    {
        return $this->belongsTo(Bahagian::class, 'bahagian_id');
    }

    /**
     * Scope: tapis bilik mengikut bahagian pengguna + tetapan cross-booking.
     *
     * Logik tiga peringkat:
     *   1. Pentadbir sistem → nampak SEMUA bilik (tiada tapis)
     *   2. Master switch ON + bahagian cross_booking_aktif=1 → bilik sendiri + bilik bahagian luar
     *   3. Default → hanya bilik bahagian sendiri (atau NULL = bahagian utama)
     */
    public function scopeUntukPengguna(Builder $query, User $pengguna): Builder
    {
        // Pentadbir sistem — akses penuh tanpa tapis
        if ($pengguna->isPentadbir()) {
            return $query;
        }

        $bahagianId = $pengguna->bahagian_id;
        $masterAktif = \App\Models\Tetapan::get('cross_booking_aktif', '0') === '1';

        if ($masterAktif) {
            // Bilik bahagian sendiri ATAU bahagian luar yang cross_booking_aktif = 1
            return $query->where(function (Builder $q) use ($bahagianId) {
                $q->where('bahagian_id', $bahagianId)
                  ->orWhereNull('bahagian_id')
                  ->orWhereHas('bahagian', fn (Builder $q2) =>
                      $q2->where('cross_booking_aktif', true)->where('aktif', true)
                  );
            });
        }

        // Default: hanya bilik bahagian sendiri
        return $query->where(function (Builder $q) use ($bahagianId) {
            $q->where('bahagian_id', $bahagianId)->orWhereNull('bahagian_id');
        });
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function getKemudahanListAttribute(): string
    {
        if (empty($this->kemudahan)) {
            return '-';
        }

        return implode(', ', $this->kemudahan);
    }

    /**
     * Peratusan penggunaan bilik untuk bulan semasa.
     *
     * @deprecated BilikController menetapkan $b->peratus_penggunaan melalui withCount()
     *             bagi halaman senarai bilik — accessor ini tidak dipanggil dalam laluan
     *             utama. DashboardService pula menggunakan $penggunaanMap (satu query GROUP BY).
     *             Dikekalkan sebagai fallback sekiranya model dimuatkan secara langsung.
     */
    public function getPenggunaanBulanIniAttribute(): int
    {
        $tempahan = $this->tempahan()
            ->whereMonth('tarikh', now()->month)
            ->whereYear('tarikh', now()->year)
            ->where('status', 'diluluskan')
            ->count();

        $maxSesi = now()->daysInMonth * 2;

        return $maxSesi > 0 ? (int) round(($tempahan / $maxSesi) * 100) : 0;
    }
}
