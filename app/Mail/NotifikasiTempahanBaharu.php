<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


namespace App\Mail;

use App\Models\Tetapan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiTempahanBaharu extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $namaMesyuarat
     * @param string $tarikhLabel
     * @param array  $semuaSesi
     * @param string $bilikNama
     * @param string $pemohonNama
     * @param string $pemohonJabatan
     * @param string $noRujukan
     * @param bool   $berulang       true jika tempahan berulang
     * @param int    $jumlahSesi     total sesi (untuk berulang)
     */
    public function __construct(
        public string $namaMesyuarat,
        public string $tarikhLabel,
        public array  $semuaSesi,
        public string $bilikNama,
        public string $pemohonNama,
        public string $pemohonJabatan,
        public string $noRujukan,
        public bool   $berulang = false,
        public int    $jumlahSesi = 1,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->berulang ? '[iBook] Tempahan Berulang Baru' : '[iBook] Tempahan Baru';
        return new Envelope(
            subject: $prefix . ': ' . $this->namaMesyuarat,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi-tempahan-baharu',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
