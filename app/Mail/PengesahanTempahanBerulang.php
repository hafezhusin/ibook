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

class PengesahanTempahanBerulang extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $namaMesyuarat
     * @param string $jenisLabel    e.g. 'Mingguan' or 'Bulanan'
     * @param string $tarikhMulaLabel
     * @param string $tarikhTamatLabel
     * @param array  $semuaSesi     ['pagi'] or ['pagi','petang']
     * @param string $bilikNama
     * @param int    $bilanganPeserta
     * @param string $kategoriLabel
     * @param string $namaPengerusi
     * @param string $pemohonNama
     * @param string $pemohonEmail
     * @param int    $jumlahTarikh  number of distinct dates
     * @param int    $jumlahSesi    total sessions (dates × sesi per date)
     */
    public function __construct(
        public string $namaMesyuarat,
        public string $jenisLabel,
        public string $tarikhMulaLabel,
        public string $tarikhTamatLabel,
        public array  $semuaSesi,
        public string $bilikNama,
        public int    $bilanganPeserta,
        public string $kategoriLabel,
        public string $namaPengerusi,
        public string $pemohonNama,
        public string $pemohonEmail,
        public int    $jumlahTarikh,
        public int    $jumlahSesi,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] Pengesahan Tempahan Berulang — ' . $this->namaMesyuarat,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pengesahan-tempahan-berulang',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
