<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TempahanBerulang extends Model
{
    use HasFactory;

    /**
     * Had keras: maksimum 12 kejadian per kumpulan berulang.
     * Dikuatkuasakan dalam janaTarikh() — konsisten antara
     * controller (batch insert) dan AJAX pratonton.
     */
    const MAX_KEJADIAN = 12;

    protected $table = 'tempahan_berulang';

    protected $fillable = [
        'ulid',
        'jenis',
        'setiap_n',
        'hari_dalam_minggu',
        'tarikh_mula',
        'tarikh_tamat',
        'sesi',
        'bilik_id',
        'user_id',
        'nama_mesyuarat',
        'bilangan_peserta',
        'kategori',
        'nama_pengerusi',
        'tujuan',
    ];

    protected $casts = [
        'hari_dalam_minggu' => 'array',
        'sesi'              => 'array',
        'tarikh_mula'       => 'date',
        'tarikh_tamat'      => 'date',
    ];

    /**
     * Jana ULID secara automatik apabila rekod baharu dibuat.
     */
    protected static function booted(): void
    {
        static::creating(function (TempahanBerulang $model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    // ── Relationships ────────────────────────────────────────────────

    public function tempahan()
    {
        return $this->hasMany(Tempahan::class, 'tempahan_berulang_id');
    }

    public function bilik()
    {
        return $this->belongsTo(BilikMesyuarat::class, 'bilik_id');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Business Logic ───────────────────────────────────────────────

    /**
     * Hanya tempahan yang belum ditolak dalam kumpulan ini.
     * Digunakan untuk kiraan "X tempahan" dalam modal skop.
     */
    public function tempahanAktif()
    {
        return $this->tempahan()
            ->where('status', '!=', Tempahan::STATUS_DITOLAK)
            ->orderBy('tarikh');
    }

    /**
     * Jana semua tarikh booking berdasarkan corak ulangan.
     *
     * Had MAX_KEJADIAN (12) dikuatkuasakan di sini — berhenti terus
     * apabila had dicapai tanpa mengira tarikh_tamat.
     *
     * @return Collection<int, Carbon>
     */
    public function janaTarikh(): Collection
    {
        $tarikh = collect();
        $mula   = $this->tarikh_mula->copy()->startOfDay();
        $tamat  = $this->tarikh_tamat->copy()->endOfDay();
        $n      = max(1, (int) $this->setiap_n);

        if ($this->jenis === 'mingguan') {
            // Hari dalam minggu: 0=Ahad, 1=Isnin, ..., 6=Sabtu
            $hari = array_map('intval', $this->hari_dalam_minggu ?? [$mula->dayOfWeek]);
            sort($hari); // susun supaya tarikh dalam minggu ikut urutan

            // Mulakan dari awal minggu yang mengandungi tarikh_mula
            // Guna Carbon::SUNDAY (0) kerana Malaysia: minggu bermula Ahad
            $mingguSekarang = $mula->copy()->startOfWeek(Carbon::SUNDAY);

            while ($mingguSekarang->lte($tamat)) {
                foreach ($hari as $dow) {
                    $calon = $mingguSekarang->copy()->addDays($dow);
                    if ($calon->gte($mula) && $calon->lte($tamat)) {
                        $tarikh->push($calon->copy()->startOfDay());
                        if ($tarikh->count() >= self::MAX_KEJADIAN) {
                            return $tarikh;
                        }
                    }
                }
                $mingguSekarang->addWeeks($n);
            }
        } else {
            // Bulanan: tarikh yang sama setiap bulan
            // Carbon::addMonths() menangani hujung bulan secara automatik
            // (cth: 31 Jan + 1 bulan = 28/29 Feb)
            $calon = $mula->copy();
            while ($calon->lte($tamat)) {
                $tarikh->push($calon->copy()->startOfDay());
                if ($tarikh->count() >= self::MAX_KEJADIAN) {
                    break;
                }
                $calon->addMonths($n);
            }
        }

        return $tarikh;
    }
}
