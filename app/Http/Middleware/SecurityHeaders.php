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


namespace App\Http\Middleware;

use App\Services\CspNonce;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cegah clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Cegah XSS (lapisan browser lama)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Kawal maklumat referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Kawal ciri-ciri browser
        $response->headers->set('Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Content Security Policy
        // connect-src include http: & https: untuk sokong fetch API (awam/bilik, awam/events)
        // pada apa-apa port (localhost:8000 atau domain sebenar)
        $host      = $request->getHost();
        $port      = $request->getPort();
        $scheme    = $request->getScheme();
        $origin    = $scheme . '://' . $host . ($port && !in_array($port, [80, 443]) ? ':' . $port : '');

        $nonce = CspNonce::get();

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' cdn.tailwindcss.com cdn.jsdelivr.net cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' cdn.tailwindcss.com cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com",
            "font-src 'self' cdnjs.cloudflare.com fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self' {$origin} cdn.jsdelivr.net",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS — hanya aktifkan jika HTTPS
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Buang header yang mendedahkan maklumat server
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
