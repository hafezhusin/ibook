<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 */

namespace App\Mail;

use App\Models\Tempahan;
use App\Models\Tetapan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TempahanDitolak extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tempahan $tempahan,
        public User $penolak,
        public string $catatan,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] Tempahan Tidak Diluluskan — '.$this->tempahan->no_rujukan,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tempahan-ditolak',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
