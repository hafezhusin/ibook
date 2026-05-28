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
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tidak dibenarkan.'], 401);
            }

            return redirect()->route('login')->with('error', 'Sila log masuk untuk meneruskan.');
        }

        // Semak jika akaun ini perlu dipaksa log keluar (peranan/status berubah)
        $userId = Auth::id();
        if (Cache::has("paksa_log_keluar_{$userId}")) {
            Cache::forget("paksa_log_keluar_{$userId}");
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akaun anda telah dikemaskini oleh pentadbir. Sila log masuk semula.');
        }

        return $next($request);
    }
}
