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


namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditLogger — Pencatat Aktiviti Sistem dengan Rantai Hash SHA-256
 *
 * Setiap rekod audit menyimpan:
 *   - prev_hash   : hash rekod sebelumnya (null untuk rekod pertama)
 *   - record_hash : SHA-256 bagi semua medan kanonik rekod ini
 *
 * Integriti boleh disahkan dengan: php artisan audit:verify
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
            $masa       = now();
            $penggunaId = Auth::id();
            $ip         = Request::ip();
            $peneranganFinal = $penerangan ?? self::janaPenerangan($tindakan, $model);
            $butiranFinal    = empty($butiran) ? null : $butiran;

            // Dapatkan hash rekod terkini untuk membentuk rantai
            $prevHash = ActivityLog::latest('dicipta_pada')
                ->latest('id')
                ->value('record_hash');

            // Bina kanonikal rekod ini untuk pengiraan hash
            $kanonikal = json_encode([
                'pengguna_id' => $penggunaId,
                'tindakan'    => $tindakan,
                'model_jenis' => $model ? class_basename($model) : null,
                'model_id'    => $model?->getKey(),
                'penerangan'  => $peneranganFinal,
                'butiran'     => $butiranFinal,
                'ip_address'  => $ip,
                'prev_hash'   => $prevHash,
                'dicipta_pada'=> $masa->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE);

            $recordHash = hash('sha256', $kanonikal);

            ActivityLog::create([
                'pengguna_id'  => $penggunaId,
                'tindakan'     => $tindakan,
                'model_jenis'  => $model ? class_basename($model) : null,
                'model_id'     => $model?->getKey(),
                'penerangan'   => $peneranganFinal,
                'butiran'      => $butiranFinal,
                'ip_address'   => $ip,
                'prev_hash'    => $prevHash,
                'record_hash'  => $recordHash,
                'dicipta_pada' => $masa,
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
        // @phpstan-ignore-next-line nullsafe.neverNull — boleh dipanggil dari konteks tanpa auth (cron/sistem)
        $namaPengguna = Auth::user()?->name ?? 'Sistem';
        $namaModel    = $model ? class_basename($model) . ' #' . $model->getKey() : '';

        return match ($tindakan) {
            'buat_tempahan'         => "{$namaPengguna} membuat tempahan baru {$namaModel}",
            'kemaskini_tempahan'    => "{$namaPengguna} mengemaskini tempahan {$namaModel}",
            'eksport_pdf'           => "{$namaPengguna} mengeksport senarai tempahan (PDF)",
            'eksport_excel'         => "{$namaPengguna} mengeksport senarai tempahan (Excel)",
            'eksport_laporan_pdf'   => "{$namaPengguna} mengeksport laporan statistik (PDF)",
            'eksport_laporan_excel' => "{$namaPengguna} mengeksport laporan statistik (Excel)",
            'eksport_audit_excel'   => "{$namaPengguna} mengeksport log audit (Excel)",
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
            'bulk_nyahaktifkan'          => "{$namaPengguna} menyahaktifkan pelbagai pengguna",
            'akses_laporan'              => "{$namaPengguna} mengakses laporan sistem",
            'buat_tempahan_berulang'     => "{$namaPengguna} membuat tempahan berulang {$namaModel}",
            'kemaskini_kumpulan_berulang'=> "{$namaPengguna} mengemaskini kumpulan tempahan berulang {$namaModel}",
            'padam_tempahan'             => "{$namaPengguna} memadam tempahan {$namaModel}",
            'backup_database'            => "{$namaPengguna} membuat backup database",
            'kemaskini_jadual_backup'    => "{$namaPengguna} mengemaskini jadual backup database",
            'muat_turun_backup'          => "{$namaPengguna} memuat turun fail backup database",
            'padam_backup'               => "{$namaPengguna} memadam rekod backup database",
            'log_masuk_berjaya'          => "{$namaPengguna} log masuk ke sistem",
            'log_masuk_gagal'            => "Percubaan log masuk gagal",
            'percubaan_akaun_nyahaktif'  => "Percubaan log masuk pada akaun yang dinyahaktifkan",
            'log_keluar'                 => "{$namaPengguna} log keluar dari sistem",
            'aktifkan_2fa'               => "{$namaPengguna} mengaktifkan pengesahan dua faktor",
            'nyahaktifkan_2fa'           => "{$namaPengguna} menyahaktifkan pengesahan dua faktor",
            default                      => "{$namaPengguna} melaksanakan tindakan: {$tindakan}",
        };
    }
}
