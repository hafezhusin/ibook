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

namespace App\Mail;

use App\Models\Tetapan;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifikasi kepada pemohon apabila tempahan mereka dibatalkan secara
 * automatik ekoran bahagian pemilik bilik dinyahaktifkan.
 */
class PembatalanTempahanOtomatik extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $pemohonNama       Nama penuh pemohon
     * @param  string  $pemohonEmail      E-mel pemohon
     * @param  Collection  $tempahanDibatal  Koleksi Tempahan yang dibatalkan (sudah load bilik)
     * @param  string  $bahagianKod       Kod bahagian yang dinyahaktifkan
     * @param  string  $tarikhBatal       Tarikh pembatalan (format d/m/Y)
     */
    public function __construct(
        public string $pemohonNama,
        public string $pemohonEmail,
        public Collection $tempahanDibatal,
        public string $bahagianKod,
        public string $tarikhBatal,
    ) {}

    public function envelope(): Envelope
    {
        $jumlah = $this->tempahanDibatal->count();
        $subjek = $jumlah > 1
            ? "[iBook] {$jumlah} Tempahan Anda Telah Dibatalkan Secara Automatik"
            : '[iBook] Tempahan Anda Telah Dibatalkan Secara Automatik';

        return new Envelope(subject: $subjek);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pembatalan-tempahan-otomatik',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
