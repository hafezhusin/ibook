<?php

namespace App\Providers;

use App\Models\Tempahan;
use App\Policies\TempahanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Kongsi tetapan sistem ke semua view
        \Illuminate\Support\Facades\View::share('tetapan', \App\Models\Tetapan::getAll());

        // Daftarkan Policy
        Gate::policy(Tempahan::class, TempahanPolicy::class);

        // Route model binding Tempahan menggunakan ULID (bukan integer ID awam)
        // URL: /tempahan/{ulid} — selamat, tidak sequential, tidak boleh diramal
        \Illuminate\Support\Facades\Route::bind('tempahan', function (string $nilai) {
            return Tempahan::where('ulid', $nilai)->firstOrFail();
        });
    }
}
