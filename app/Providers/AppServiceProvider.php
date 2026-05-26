<?php

/**
 * iBook — Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */

namespace App\Providers;

use App\Models\Tempahan;
use App\Models\TempahanBerulang;
use App\Policies\TempahanPolicy;
use App\Services\CspNonce;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Domain Lock — hanya aktif dalam HTTP request production ──────────
        // Perisian ini berlesen hanya untuk domain yang didaftarkan.
        // runningInConsole() = false semasa artisan migrate, queue, dll — jangan blok CLI.
        if (app()->environment('production') && !app()->runningInConsole()) {
            $domainDibenar = ['ibookbptm.great-site.net'];
            $domainSemasa  = request()->getHost();
            if (!in_array($domainSemasa, $domainDibenar, true)) {
                abort(403, 'Perisian iBook tidak berlesen untuk domain ini. '
                    . 'Hubungi Bahagian Pengurusan Teknologi Maklumat (BPTM) untuk maklumat lanjut.');
            }
        }

        // Kongsi tetapan sistem ke semua view
        \Illuminate\Support\Facades\View::share('tetapan', \App\Models\Tetapan::getAll());

        // Kongsi CSP nonce ke semua view — untuk <script nonce="{{ $cspNonce }}">
        \Illuminate\Support\Facades\View::share('cspNonce', CspNonce::get());

        // Daftarkan Policy
        Gate::policy(Tempahan::class, TempahanPolicy::class);
        // TempahanBerulang guna semula TempahanPolicy (hak akses sama)
        Gate::policy(TempahanBerulang::class, TempahanPolicy::class);

        // Route model binding Tempahan menggunakan ULID (bukan integer ID awam)
        // URL: /tempahan/{ulid} — selamat, tidak sequential, tidak boleh diramal
        \Illuminate\Support\Facades\Route::bind('tempahan', function (string $nilai) {
            return Tempahan::where('ulid', $nilai)->firstOrFail();
        });

        // Route model binding TempahanBerulang menggunakan ULID
        \Illuminate\Support\Facades\Route::bind('kumpulan', function (string $nilai) {
            return TempahanBerulang::where('ulid', $nilai)->firstOrFail();
        });
    }
}
