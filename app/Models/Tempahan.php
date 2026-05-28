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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property-read BilikMesyuarat|null  $bilik           Bilik yang ditempah
 * @property-read User|null           $pengguna        Pemohon tempahan
 * @property-read User|null           $pelulus         Pegawai yang meluluskan
 * @property-read User|null           $pengubah        Pegawai yang mengemaskini
 * @property-read TempahanBerulang|null $kumpulanBerulang Kumpulan tempahan berulang (jika ada)
 * @property string $no_rujukan Accessor: TMP-{tahun}-{ulid_suffix}
 * @property string $masa_label Accessor: "HH:MM - HH:MM"
 * @property string $status_badge Accessor: HTML badge
 * @property string $kategori_label Accessor: label mesyuarat/bengkel/dll
 */
class Tempahan extends Model
{
    use HasFactory;

    protected $table = 'tempahan';

    const STATUS_DILULUSKAN = 'diluluskan';

    const STATUS_DITOLAK = 'ditolak';

    const SESI_PAGI = 'pagi';

    const SESI_PETANG = 'petang';

    const MASA_SESI = [
        'pagi' => ['mula' => '09:00', 'tamat' => '13:00', 'label' => 'SESI PAGI (9:00 AM - 1:00 PM)'],
        'petang' => ['mula' => '14:00', 'tamat' => '18:00', 'label' => 'SESI PETANG (2:00 PM - 6:00 PM)'],
    ];

    const KATEGORI = [
        'mesyuarat' => 'Mesyuarat',
        'perbincangan' => 'Perbincangan',
        'taklimat' => 'Taklimat',
        'bengkel' => 'Bengkel/Workshop',
        'latihan' => 'Latihan/Kursus',
    ];

    /**
     * Dapatkan konfigurasi sesi dari config/ibook.php.
     * Fallback ke const MASA_SESI supaya backward compatible.
     */
    public static function masaSesi(string $sesi): array
    {
        return config("ibook.sesi.{$sesi}", self::MASA_SESI[$sesi] ?? []);
    }

    /**
     * Jana ULID secara automatik apabila rekod baharu dibuat.
     */
    protected static function booted(): void
    {
        static::creating(function (Tempahan $tempahan) {
            if (empty($tempahan->ulid)) {
                $tempahan->ulid = (string) Str::ulid();
            }
        });
    }

    protected $fillable = [
        'ulid',
        'tempahan_berulang_id',
        'nama_mesyuarat',
        'tarikh',
        'sesi',
        'masa_mula',
        'masa_tamat',
        'bilik_id',
        'user_id',
        'bilangan_peserta',
        'kategori',
        'nama_pengerusi',
        'tujuan',
        'status',
        'catatan_penolakan',
        'diluluskan_oleh',
        'diluluskan_pada',
        'dikemaskini_oleh',
        'dikemaskini_pada',
    ];

    protected $casts = [
        'tarikh' => 'date',
        'diluluskan_pada' => 'datetime',
        'dikemaskini_pada' => 'datetime',
    ];

    /**
     * Pastikan route() helper Jana URL dengan ULID, bukan ID integer.
     * Contoh: route('tempahan.show', $model) → /tempahan/01HZ...
     * Route::bind('tempahan', ...) dalam AppServiceProvider mengendalikan
     * resolusi ULID → model.
     */
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function bilik(): BelongsTo
    {
        return $this->belongsTo(BilikMesyuarat::class, 'bilik_id');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelulus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diluluskan_oleh');
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikemaskini_oleh');
    }

    public function kumpulanBerulang(): BelongsTo
    {
        return $this->belongsTo(TempahanBerulang::class, 'tempahan_berulang_id');
    }

    /**
     * Semak sama ada pengguna dibenarkan mengedit/melihat tempahan ini.
     * Staf boleh edit tempahan sendiri ATAU rakan seunit (jabatan sama).
     * Pentadbir & Urus Setia boleh edit semua.
     */
    public function bolehDiEditOleh(User $user): bool
    {
        if (! $user->isStaf()) {
            return true;
        }
        if ($this->user_id === $user->id) {
            return true;
        }

        // Rakan seunit — semak jabatan pemohon asal
        return $user->jabatan &&
               $this->pengguna &&
               $this->pengguna->jabatan === $user->jabatan;
    }

    /**
     * Nombor rujukan unik: TMP-{tahun}-{8 aksara dari ULID}
     * Contoh: TMP-2026-A3F9B2C1
     * ULID tidak sequential — selamat daripada enumeration attack.
     * Fallback ke hash MD5 ID jika ULID belum ada (rekod lama).
     */
    public function getNoRujukanAttribute(): string
    {
        // tarikh/created_at boleh null sebelum save — nullsafe dan coalesce adalah pertahanan defensif.
        // @phpstan-ignore-next-line nullsafe.neverNull, nullCoalesce.expr
        $year = $this->tarikh?->year ?? $this->created_at?->year ?? now()->year;
        $suffix = $this->ulid
            ? strtoupper(substr($this->ulid, -8))
            : strtoupper(substr(hash('sha256', $this->id.'ibook_ref'), 0, 8));

        return 'TMP-'.$year.'-'.$suffix;
    }

    public function getMasaLabelAttribute(): string
    {
        $mula = substr($this->masa_mula, 0, 5);
        $tamat = substr($this->masa_tamat, 0, 5);

        return "$mula - $tamat";
    }

    public function getStatusBadgeAttribute(): string
    {
        // PHPStan mengetahui status adalah 'diluluskan'|'ditolak' sahaja.
        // Semak satu kes, pulang yang satu lagi sebagai default — tiada perbandingan mubazir.
        if ($this->status === self::STATUS_DILULUSKAN) {
            return '<span class="badge-lulus">Diluluskan</span>';
        }

        return '<span class="badge-tolak">Ditolak</span>';
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function isDiluluskan(): bool
    {
        return $this->status === self::STATUS_DILULUSKAN;
    }
}
