<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditLogger — Pencatat Aktiviti Sistem
 *
 * Guna: AuditLogger::catat('buat_tempahan', $tempahan, ['nota' => '...']);
 */
class AuditLogger
{
    /**
     * Catat satu aktiviti ke jadual activity_log.
     *
     * @param  string       $tindakan   Kod tindakan (snake_case)
     * @param  Model|null   $model      Rekod yang terkesan (optional)
     * @param  array        $butiran    Data tambahan (optional)
     * @param  string|null  $penerangan Teks penerangan (auto-jana jika null)
     */
    public static function catat(
        string $tindakan,
        ?Model $model = null,
        array $butiran = [],
        ?string $penerangan = null
    ): void {
        try {
            ActivityLog::create([
                'pengguna_id'  => Auth::id(),
                'tindakan'     => $tindakan,
                'model_jenis'  => $model ? class_basename($model) : null,
                'model_id'     => $model?->getKey(),
                'penerangan'   => $penerangan ?? self::janaPenerangan($tindakan, $model),
                'butiran'      => empty($butiran) ? null : $butiran,
                'ip_address'   => Request::ip(),
                'dicipta_pada' => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan biarkan kegagalan logging pecahkan aliran utama
            \Illuminate\Support\Facades\Log::warning('AuditLogger gagal: ' . $e->getMessage());
        }
    }

    /**
     * Jana penerangan ringkas automatik berdasarkan tindakan.
     */
    private static function janaPenerangan(string $tindakan, ?Model $model): string
    {
        $namaPengguna = Auth::user()?->name ?? 'Sistem';
        $namaModel    = $model ? class_basename($model) . ' #' . $model->getKey() : '';

        return match ($tindakan) {
            'buat_tempahan'         => "{$namaPengguna} membuat tempahan baru {$namaModel}",
            'kemaskini_tempahan'    => "{$namaPengguna} mengemaskini tempahan {$namaModel}",
            'eksport_pdf'           => "{$namaPengguna} mengeksport senarai tempahan (PDF)",
            'eksport_excel'         => "{$namaPengguna} mengeksport senarai tempahan (Excel)",
            'tambah_pengguna'       => "{$namaPengguna} menambah pengguna baru {$namaModel}",
            'kemaskini_pengguna'    => "{$namaPengguna} mengemaskini maklumat pengguna {$namaModel}",
            'reset_kata_laluan'     => "{$namaPengguna} menetapkan semula kata laluan pengguna {$namaModel}",
            'tukar_kata_laluan'     => "{$namaPengguna} menukar kata laluan sendiri",
            'aktifkan_pengguna'     => "{$namaPengguna} mengaktifkan akaun pengguna {$namaModel}",
            'nyahaktifkan_pengguna' => "{$namaPengguna} menyahaktifkan akaun pengguna {$namaModel}",
            'tambah_bilik'          => "{$namaPengguna} menambah bilik baru {$namaModel}",
            'kemaskini_bilik'       => "{$namaPengguna} mengemaskini maklumat bilik {$namaModel}",
            'padam_bilik'           => "{$namaPengguna} memadam bilik {$namaModel}",
            'kemaskini_tetapan'     => "{$namaPengguna} mengemaskini tetapan sistem",
            'kemaskini_profil'      => "{$namaPengguna} mengemaskini profil sendiri",
            'bulk_aktifkan'         => "{$namaPengguna} mengaktifkan pelbagai pengguna",
            'bulk_nyahaktifkan'     => "{$namaPengguna} menyahaktifkan pelbagai pengguna",
            'akses_laporan'         => "{$namaPengguna} mengakses laporan sistem",
            default                 => "{$namaPengguna} melaksanakan tindakan: {$tindakan}",
        };
    }
}
