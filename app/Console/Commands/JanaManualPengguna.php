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

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class JanaManualPengguna extends Command
{
    protected $signature = 'manual:jana';

    protected $description = 'Jana Manual Pengguna Staf PDF';

    public function handle(): int
    {
        $this->info('Menjana Manual Pengguna Staf...');

        $screenshots = [
            '01-login.jpg', '02-dashboard.jpg', '03-dashboard-bawah.jpg',
            '04-senarai-tempahan.jpg', '05-tempahan-baru-atas.jpg',
            '06-tempahan-baru-tengah.jpg', '07-tempahan-baru-bawah.jpg',
            '08-tempahan-berulang.jpg', '09-semak-ketersediaan.jpg',
            '10-laporan.jpg', '11-kalendar.jpg', '12-profil.jpg',
        ];

        foreach ($screenshots as $file) {
            if (! file_exists(public_path("docs/screenshots/{$file}"))) {
                $this->error("Screenshot tidak dijumpai: {$file}");
                $this->line('Jalankan: node capture-manual-screenshots.mjs');

                return self::FAILURE;
            }
        }

        $pdf = Pdf::loadView('manual.pengguna-staf')
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
                'dpi' => 96,
            ]);

        $output = $pdf->output();
        $dest = public_path('docs/Manual_Pengguna_Staf_iBook2.pdf');

        file_put_contents($dest, $output);

        $sizeMb = round(strlen($output) / 1024 / 1024, 2);
        $this->info("✓ PDF berjaya: public/docs/Manual_Pengguna_Staf_iBook2.pdf ({$sizeMb} MB)");

        return self::SUCCESS;
    }
}
