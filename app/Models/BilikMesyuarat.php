<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BilikMesyuarat extends Model
{
    use HasFactory;

    protected $table = 'bilik_mesyuarat';

    protected $fillable = [
        'nama',
        'kapasiti',
        'kemudahan',
        'status',
        'gambar',
        'lokasi',
    ];

    protected $casts = [
        'kemudahan' => 'array',
        'kapasiti' => 'integer',
    ];

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

    public function getPenggunaanBulanIniAttribute(): int
    {
        $tempahan = $this->tempahan()
            ->whereMonth('tarikh', now()->month)
            ->whereYear('tarikh', now()->year)
            ->where('status', 'diluluskan')
            ->count();

        $maxHari = now()->daysInMonth;
        $maxSesi = $maxHari * 2;

        return $maxSesi > 0 ? (int) round(($tempahan / $maxSesi) * 100) : 0;
    }
}
