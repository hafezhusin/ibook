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


namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetKataLaluan extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Permintaan Set Semula Kata Laluan — ' . config('app.name'))
            ->greeting('Salam ' . $notifiable->name . ',')
            ->line('Kami telah menerima permintaan untuk menetapkan semula kata laluan akaun iBook 2.0 anda.')
            ->action('Set Semula Kata Laluan', $url)
            ->line('Pautan ini akan tamat tempoh dalam **60 minit**.')
            ->line('Jika anda tidak membuat permintaan ini, sila abaikan e-mel ini. Akaun anda selamat.')
            ->salutation('Sekian, pasukan ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
