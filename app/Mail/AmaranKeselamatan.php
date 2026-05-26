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

class AmaranKeselamatan extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string   $ip          Alamat IP penyerang
     * @param int      $kiraan      Bilangan percubaan gagal dalam 1 jam
     * @param string[] $emelDicuba  Senarai emel yang dicuba (max 5)
     */
    public function __construct(
        public string $ip,
        public int    $kiraan,
        public array  $emelDicuba = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[iBook] ⚠️ Amaran Keselamatan: ' . $this->kiraan . ' percubaan log masuk dari ' . $this->ip,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.amaran-keselamatan',
            with: ['tetapan' => Tetapan::getAll()],
        );
    }
}
