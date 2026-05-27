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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BilikMesyuarat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bilik_mesyuarat';

    protected $fillable = [
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

    public function tempahan()
    {
        return $this->hasMany(Tempahan::class, 'bilik_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function getKemudahanListAttribute(): string
    {
        if (empty($this->kemudahan)) return '-';
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
