<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tempahan extends Model
{
    use HasFactory;

    protected $table = 'tempahan';

    const STATUS_DILULUSKAN = 'diluluskan';
    const STATUS_DITOLAK = 'ditolak';

    const SESI_PAGI = 'pagi';
    const SESI_PETANG = 'petang';

    const MASA_SESI = [
        'pagi'   => ['mula' => '09:00', 'tamat' => '13:00', 'label' => 'SESI PAGI (9:00 AM - 1:00 PM)'],
        'petang' => ['mula' => '14:00', 'tamat' => '18:00', 'label' => 'SESI PETANG (2:00 PM - 6:00 PM)'],
    ];

    const KATEGORI = [
        'pengurusan' => 'Mesyuarat Pengurusan',
        'teknikal'   => 'Mesyuarat Teknikal',
        'taklimat'   => 'Taklimat',
        'bengkel'    => 'Bengkel / Workshop',
        'latihan'    => 'Latihan',
        'lain'       => 'Lain-lain',
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
        'tarikh'          => 'date',
        'diluluskan_pada' => 'datetime',
        'dikemaskini_pada'=> 'datetime',
    ];

    public function bilik()
    {
        return $this->belongsTo(BilikMesyuarat::class, 'bilik_id');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pelulus()
    {
        return $this->belongsTo(User::class, 'diluluskan_oleh');
    }

    public function pengubah()
    {
        return $this->belongsTo(User::class, 'dikemaskini_oleh');
    }

    public function kumpulanBerulang()
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
        if (!$user->isStaf()) return true;
        if ($this->user_id === $user->id) return true;

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
        $year   = $this->tarikh?->year ?? $this->created_at?->year ?? now()->year;
        $suffix = $this->ulid
            ? strtoupper(substr($this->ulid, -8))
            : strtoupper(substr(md5($this->id . 'ibook_ref'), 0, 8));
        return 'TMP-' . $year . '-' . $suffix;
    }

    public function getMasaLabelAttribute(): string
    {
        $mula = substr($this->masa_mula, 0, 5);
        $tamat = substr($this->masa_tamat, 0, 5);
        return "$mula - $tamat";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DILULUSKAN => '<span class="badge-lulus">Diluluskan</span>',
            self::STATUS_DITOLAK => '<span class="badge-tolak">Ditolak</span>',
            default => '-',
        };
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
