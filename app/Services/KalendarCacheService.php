<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 */

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Pengurusan versi cache kalendar.
 *
 * KalendarController menggunakan nombor versi ini sebagai sebahagian daripada
 * kunci cache. Bila versi ditambah, cache lama secara automatik tidak digunakan
 * tanpa perlu memadamkan kunci secara eksplisit.
 *
 * Dipanggil setiap kali tempahan dibuat, dikemaskini, atau dipadam —
 * termasuk tempahan berulang.
 */
class KalendarCacheService
{
    public static function bump(): void
    {
        Cache::add('kalendar:events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:events:version');
        Cache::add('kalendar:public-events:version', 1, now()->addDays(30));
        Cache::increment('kalendar:public-events:version');
    }
}
