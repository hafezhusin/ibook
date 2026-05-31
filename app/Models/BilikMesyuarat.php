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
     * Logik:
     *   - Bilik dari bahagian yang NYAHAKTIF (aktif=false) → tersembunyi untuk SEMUA peranan
     *     (kecuali halaman pengurusan bilik admin yang tidak guna scope ini)
     *   - Pentadbir sistem → nampak semua bilik dari bahagian aktif + bilik tanpa bahagian
     *   - Master switch ON + bahagian cross_booking_aktif=1 → bilik sendiri + bilik bahagian luar (aktif)
     *   - Default → hanya bilik bahagian sendiri (bahagian mesti aktif)
     */
    public function scopeUntukPengguna(Builder $query, User $pengguna): Builder
    {
        // Closure bantu: tapis bilik dari bahagian yang nyahaktif
        // Bilik tanpa bahagian (NULL) kekal kelihatan — tiada kaitan dengan bahagian
        $bahagianAktifSahaja = function (Builder $q) {
            $q->whereNull('bahagian_id')
              ->orWhereHas('bahagian', fn (Builder $q2) => $q2->where('aktif', true));
        };

        // Pentadbir sistem — nampak semua bilik KECUALI bilik dari bahagian nyahaktif
        // (halaman pengurusan BilikController::index() tidak guna scope ini — kekal tunjuk semua)
        if ($pengguna->isPentadbir()) {
            return $query->where($bahagianAktifSahaja);
        }

        $bahagianId = $pengguna->bahagian_id;

        // Pengguna belum ditetapkan bahagian — beri akses sementara (bahagian aktif sahaja)
        // (admin perlu assign bahagian kepada pengguna ini)
        if (! $bahagianId) {
            return $query->where($bahagianAktifSahaja);
        }

        $masterAktif = \App\Models\Tetapan::get('cross_booking_aktif', '0') === '1';

        if ($masterAktif) {
            // Bilik bahagian sendiri (aktif) ATAU bahagian luar yang cross_booking_aktif=1 (aktif)
            // Bilik tanpa bahagian (NULL) TIDAK dikongsi — perlu assign dahulu
            return $query->where(function (Builder $q) use ($bahagianId) {
                $q->where(function (Builder $q2) use ($bahagianId) {
                    $q2->where('bahagian_id', $bahagianId)
                       ->whereHas('bahagian', fn (Builder $q3) => $q3->where('aktif', true));
                })
                ->orWhereHas('bahagian', fn (Builder $q2) =>
                    $q2->where('cross_booking_aktif', true)->where('aktif', true)
                );
            });
        }

        // Default: bilik bahagian sendiri sahaja, bahagian mesti aktif
        // Bilik tanpa bahagian_id (NULL) TIDAK ditunjukkan — perlu ditetapkan dahulu
        return $query->where('bahagian_id', $bahagianId)
                     ->whereHas('bahagian', fn (Builder $q) => $q->where('aktif', true));
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
