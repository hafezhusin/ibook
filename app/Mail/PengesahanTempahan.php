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

class PengesahanTempahan extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $noRujukan     e.g. TMP-2026-A3F9B2C1
     * @param string $namaMesyuarat
     * @param string $tarikhLabel   formatted date string
     * @param array  $semuaSesi     ['pagi'] or ['pagi','petang']
     * @param string $bilikNama
     * @param int    $bilanganPeserta
     * @param string $kategoriLabel
     * @param string $namaPengerusi
     * @param string $tujuan
     * @param string $pemohonNama
     * @param string $pemohonEmail
     */
    public function __construct(
        public string $noRujukan,
        public string $namaMesyuarat,
        public string $tarikhLabel,
        public array  $semuaSesi,
        public string $bilikNama,
        public int    $bilanganPeserta,
        public string $kategoriLabel,
        public string $namaPengerusi,
        public string $tujuan,
        public string $pemohonNama,
        public string $pemohonEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] Pengesahan Tempahan — ' . $this->noRujukan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pengesahan-tempahan',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
