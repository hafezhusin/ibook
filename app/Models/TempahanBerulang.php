<?php

/**
 * iBook — Sistem Pengurusan Bilik Mesyuarat
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

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $ulid
 * @property string $jenis 'mingguan'|'bulanan'
 * @property int $setiap_n
 * @property array|null $hari_dalam_minggu [0-6], mingguan sahaja
 * @property Carbon $tarikh_mula
 * @property Carbon $tarikh_tamat
 * @property array $sesi ['pagi']|['pagi','petang']
 * @property int $bilik_id
 * @property int $user_id
 * @property string $nama_mesyuarat
 * @property int $bilangan_peserta
 * @property string $kategori
 * @property string $nama_pengerusi
 * @property string|null $tujuan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read BilikMesyuarat                                                 $bilik
 * @property-read User                                                            $pengguna
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tempahan>        $tempahan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tempahan>        $tempahanAktif
 */
class TempahanBerulang extends Model
{
    protected $table = 'tempahan_berulang';

    /** Had keras bilangan kejadian per kumpulan berulang. */
    const MAX_KEJADIAN = 12;

    protected static function booted(): void
    {
        static::creating(function (TempahanBerulang $m): void {
            if (empty($m->ulid)) {
                $m->ulid = (string) Str::ulid();
            }
        });
    }

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
        'tarikh_mula' => 'date',
        'tarikh_tamat' => 'date',
        'hari_dalam_minggu' => 'array',
        'sesi' => 'array',
    ];

    /**
     * Route model binding menggunakan ULID (bukan ID integer).
     * Disokong oleh Route::bind('kumpulan', ...) dalam AppServiceProvider.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    // ── Relationships ──────────────────────────────────────────────────

    /** Semua tempahan individu dalam kumpulan ini. */
    public function tempahan(): HasMany
    {
        return $this->hasMany(Tempahan::class, 'tempahan_berulang_id');
    }

    /**
     * Tempahan yang masih aktif (belum ditolak).
     * Digunakan untuk kira bilangan dan kemaskini kolektif.
     */
    public function tempahanAktif(): HasMany
    {
        return $this->hasMany(Tempahan::class, 'tempahan_berulang_id')
            ->whereNotIn('status', [Tempahan::STATUS_DITOLAK, Tempahan::STATUS_DIBATALKAN]);
    }

    public function bilik(): BelongsTo
    {
        return $this->belongsTo(BilikMesyuarat::class, 'bilik_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Logik Jana Tarikh ──────────────────────────────────────────────

    /**
     * Jana semua tarikh kejadian berdasarkan tetapan kumpulan.
     *
     * Had keras: config('ibook.berulang.had_kejadian') = 12.
     * Mingguan: iterasi minggu demi minggu, ambil hari dari hari_dalam_minggu[].
     * Bulanan:  tambah N bulan setiap kali — Carbon urus hujung bulan (Feb 28/29).
     *
     * @return Collection<int, CarbonImmutable>
     */
    public function janaTarikh(): Collection
    {
        $had = (int) config('ibook.berulang.had_kejadian', self::MAX_KEJADIAN);
        $semua = collect();
        $mula = CarbonImmutable::parse($this->tarikh_mula)->startOfDay();
        $tamat = CarbonImmutable::parse($this->tarikh_tamat)->startOfDay();
        $setiapN = max(1, (int) $this->setiap_n);

        if ($this->jenis === 'mingguan') {
            $hari = array_map('intval', (array) ($this->hari_dalam_minggu ?? []));
            sort($hari);

            if (empty($hari)) {
                return $semua;
            }

            // Mulakan dari awal minggu (Ahad = 0) supaya semua hari dinilai —
            // termasuk hari sebelum $mula dalam minggu yang sama.
            $mingguKursor = $mula->startOfWeek(Carbon::SUNDAY);

            while ($mingguKursor->lte($tamat) && $semua->count() < $had) {
                foreach ($hari as $dow) {
                    $calon = $mingguKursor->addDays($dow);
                    if ($calon->gte($mula) && $calon->lte($tamat) && $semua->count() < $had) {
                        $semua->push($calon);
                    }
                }
                $mingguKursor = $mingguKursor->addWeeks($setiapN);
            }
        } else {
            // Bulanan
            $bulan = 0;

            while ($semua->count() < $had) {
                $calon = $mula->addMonths($bulan);
                if ($calon->gt($tamat)) {
                    break;
                }
                $semua->push($calon);
                $bulan += $setiapN;
            }
        }

        return $semua
            ->sortBy(fn (CarbonImmutable $t) => $t->timestamp)
            ->values();
    }
}
