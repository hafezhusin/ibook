<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiPendaftaranSSO extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $namaPengguna,
        public readonly string $emelPengguna,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] Pendaftaran Baharu Menunggu Kelulusan — '.$this->emelPengguna,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi-pendaftaran-sso',
        );
    }
}
