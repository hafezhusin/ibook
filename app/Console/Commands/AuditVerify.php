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


namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

/**
 * php artisan audit:verify
 *
 * Sahkan integriti rantai hash SHA-256 dalam jadual activity_log.
 * Sebarang rekod yang telah diubah suai akan dikesan.
 */
class AuditVerify extends Command
{
    protected $signature   = 'audit:verify {--limit=1000 : Bilangan rekod terkini untuk disemak}';
    protected $description = 'Sahkan integriti rantai hash log audit (SHA-256)';

    public function handle(): int
    {
        $limit  = (int) $this->option('limit');
        $rekods = ActivityLog::orderBy('id')->take($limit)->get();

        if ($rekods->isEmpty()) {
            $this->warn('Tiada rekod audit untuk disemak.');
            return self::SUCCESS;
        }

        $this->info("Menyemak {$rekods->count()} rekod audit...");
        $this->newLine();

        $rosak       = 0;
        $prevHashSebenar = null;

        foreach ($rekods as $i => $log) {
            // Skip rekod lama (sebelum hash chain dilaksanakan)
            if ($log->record_hash === null) {
                $prevHashSebenar = null; // reset chain — rekod lama tidak bersambung
                continue;
            }

            // Bina semula kanonikal yang sama seperti dalam AuditLogger::catat()
            $kanonikal = json_encode([
                'pengguna_id' => $log->pengguna_id,
                'tindakan'    => $log->tindakan,
                'model_jenis' => $log->model_jenis,
                'model_id'    => $log->model_id,
                'penerangan'  => $log->penerangan,
                'butiran'     => $log->butiran,
                'ip_address'  => $log->ip_address,
                'prev_hash'   => $log->prev_hash,
                'dicipta_pada'=> $log->dicipta_pada->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE);

            $jangkaan = hash('sha256', $kanonikal);

            $hashSah   = hash_equals($jangkaan, $log->record_hash);
            $rantaiSah = ($prevHashSebenar === null)
                ? ($log->prev_hash === null)
                : hash_equals($prevHashSebenar, $log->prev_hash ?? '');

            if (!$hashSah || !$rantaiSah) {
                $rosak++;
                $this->error(sprintf(
                    '  [ROSAK] ID #%d | %s | %s',
                    $log->id,
                    $log->tindakan,
                    $log->dicipta_pada->format('Y-m-d H:i:s')
                ));

                if (!$hashSah) {
                    $this->line("           Hash rekod tidak sepadan (rekod mungkin diubah)");
                }
                if (!$rantaiSah) {
                    $this->line("           Rantai hash terputus (rekod sebelumnya mungkin dipadam/diubah)");
                }
            }

            $prevHashSebenar = $log->record_hash;
        }

        $this->newLine();

        if ($rosak === 0) {
            $this->info('✓ Semua rekod audit SEMPURNA — tiada gangguan dikesan.');
            return self::SUCCESS;
        }

        $this->error("⚠ {$rosak} rekod ROSAK dikesan! Sila semak log sistem dengan segera.");
        return self::FAILURE;
    }
}
