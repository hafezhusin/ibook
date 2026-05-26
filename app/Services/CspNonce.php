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


namespace App\Services;

/**
 * CspNonce — Penjana Nonce CSP (Content Security Policy)
 *
 * Menjana satu nilai rawak base64 per permintaan HTTP.
 * Nilai yang sama dikongsi antara:
 *   - SecurityHeaders middleware (untuk header CSP)
 *   - Semua view Blade ({{ $cspNonce }})
 *
 * Penggunaan dalam Blade: <script nonce="{{ $cspNonce }}">
 */
class CspNonce
{
    private static ?string $nonce = null;

    /**
     * Dapatkan (atau jana) nonce untuk permintaan semasa.
     * Dipanggil berkali-kali — nilai yang sama dikembalikan.
     */
    public static function get(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }

        return self::$nonce;
    }

    /**
     * Reset nonce (untuk testing sahaja).
     */
    public static function reset(): void
    {
        self::$nonce = null;
    }
}
