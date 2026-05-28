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
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KodOTP extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $namaPengguna  Nama penuh pengguna
     * @param  string  $otp  Kod OTP 6 digit
     */
    public function __construct(
        public string $namaPengguna,
        public string $otp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] Kod Pengesahan: '.$this->otp,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kod-otp',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
