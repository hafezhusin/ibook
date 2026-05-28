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

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuditLog Middleware — Pelaporan Peringkat HTTP
 *
 * Skop SEMPIT dan sengaja: hanya log kegagalan HTTP (4xx/5xx) bagi operasi tulis.
 * Semua event perniagaan (buat tempahan, kemaskini pengguna, dll.) sudah dilog
 * secara berstruktur oleh AuditLogger::catat() ke jadual activity_log.
 *
 * Middleware ini bertindak sebagai jaring keselamatan untuk mengesan:
 * - Percubaan tanpa kebenaran (401, 403)
 * - Ralat aplikasi semasa operasi tulis (500)
 * - Permintaan tidak sah (422) yang menunjukkan manipulasi data
 */
class AuditLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya log operasi tulis yang GAGAL — bukan semua POST/PUT/DELETE
        // Kejayaan (2xx, 3xx) sudah dilog oleh AuditLogger::catat() dalam controller
        $kaedahTulis = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $statusGagal = $response->getStatusCode() >= 400;

        if (in_array($request->method(), $kaedahTulis) && $statusGagal) {
            Log::channel('stack')->warning('HTTP_TULIS_GAGAL', [
                'user_id' => Auth::id() ?? 'tetamu',
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'status' => $response->getStatusCode(),
                'agent' => substr($request->userAgent() ?? '', 0, 100),
            ]);
        }

        return $response;
    }
}
