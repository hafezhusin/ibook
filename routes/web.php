<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BilikController;
use App\Http\Controllers\CarianController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KalendarController;
use App\Http\Controllers\KetersediaanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\TempahanBerulangController;
use App\Http\Controllers\TempahanController;
use App\Http\Controllers\TetapanController;
use Illuminate\Support\Facades\Route;

// Auth routes (tanpa middleware)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Lupa & set semula kata laluan
// throttle:5,1 = maksimum 5 percubaan per minit per IP — cegah spam emel & brute force token
Route::get('/lupa-kata-laluan', [ForgotPasswordController::class, 'showLinkForm'])->name('password.request');
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/lupa-kata-laluan', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::post('/reset-kata-laluan', [ForgotPasswordController::class, 'reset'])->name('password.update');
});
Route::get('/reset-kata-laluan/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');

// Route awam - boleh akses tanpa log masuk
// throttle:60,1 = maksimum 60 request per minit per IP
// Mencegah scraping, enumeration, dan DDoS pada endpoint terbuka
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/awam/events', [KalendarController::class, 'publicEvents'])->name('awam.events');
    Route::get('/awam/bilik', [BilikController::class, 'publicList'])->name('awam.bilik');
});

// Routes yang memerlukan log masuk
Route::middleware('auth.custom')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profil pengguna (semua peranan)
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::post('/profil/kemaskini', [ProfilController::class, 'update'])->name('profil.update');
    Route::post('/profil/kata-laluan', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // Kalendar
    Route::get('/kalendar', [KalendarController::class, 'index'])->name('kalendar');
    Route::get('/kalendar/events', [KalendarController::class, 'events'])->name('kalendar.events');

    // Carian Global
    Route::get('/carian', [CarianController::class, 'index'])->name('carian');

    // Semak Ketersediaan Bilik
    Route::get('/semak-bilik', [KetersediaanController::class, 'index'])->name('ketersediaan');
    Route::get('/semak-bilik/cek', [KetersediaanController::class, 'cek'])->name('ketersediaan.cek');

    // Tempahan
    Route::get('/tempahan', [TempahanController::class, 'index'])->name('tempahan.index');
    Route::get('/tempahan/baru', [TempahanController::class, 'create'])->name('tempahan.create');
    Route::post('/tempahan', [TempahanController::class, 'store'])->name('tempahan.store');
    Route::get('/tempahan/cek-konflik', [TempahanController::class, 'cekKonflik'])->name('tempahan.cek-konflik');
    Route::get('/tempahan/{tempahan}', [TempahanController::class, 'show'])->name('tempahan.show');
    Route::get('/tempahan/{tempahan}/edit', [TempahanController::class, 'edit'])->name('tempahan.edit');
    Route::put('/tempahan/{tempahan}', [TempahanController::class, 'update'])->name('tempahan.update');

    // Tempahan Berulang
    // Nota: 'pratonton' mesti sebelum '{kumpulan}' untuk elak routing conflict
    Route::get('/tempahan-berulang/pratonton', [TempahanBerulangController::class, 'pratonton'])
        ->name('tempahan-berulang.pratonton');
    Route::post('/tempahan-berulang', [TempahanBerulangController::class, 'store'])
        ->name('tempahan-berulang.store');
    Route::put('/tempahan-berulang/{kumpulan}', [TempahanBerulangController::class, 'update'])
        ->name('tempahan-berulang.update');
    Route::delete('/tempahan/{tempahan}/padam-berulang', [TempahanBerulangController::class, 'destroy'])
        ->name('tempahan-berulang.destroy');

    // Eksport — hanya Pentadbir Sistem & Urus Setia
    Route::middleware('role:pentadbir_sistem,urus_setia')->group(function () {
        Route::get('/tempahan/eksport/pdf', [TempahanController::class, 'exportPdf'])->name('tempahan.pdf');
        Route::get('/tempahan/eksport/excel', [TempahanController::class, 'exportExcel'])->name('tempahan.excel');
    });

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    // Pentadbir Sistem & Urus Setia — lihat pengguna & reset password
    Route::middleware('role:pentadbir_sistem,urus_setia')->group(function () {
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password');
    });

    // Hanya Pentadbir Sistem
    Route::middleware('role:pentadbir_sistem')->group(function () {
        // Bilik Mesyuarat
        Route::get('/bilik-mesyuarat', [BilikController::class, 'index'])->name('bilik.index');
        Route::get('/bilik-mesyuarat/tambah', [BilikController::class, 'create'])->name('bilik.create');
        Route::post('/bilik-mesyuarat', [BilikController::class, 'store'])->name('bilik.store');
        Route::get('/bilik-mesyuarat/{bilik}/edit', [BilikController::class, 'edit'])->name('bilik.edit');
        Route::put('/bilik-mesyuarat/{bilik}', [BilikController::class, 'update'])->name('bilik.update');
        Route::delete('/bilik-mesyuarat/{bilik}', [BilikController::class, 'destroy'])->name('bilik.destroy');

        // Pengguna — pengurusan penuh (tambah, edit, aktif/nyahaktif)
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::post('/pengguna/bulk-aktif', [PenggunaController::class, 'bulkAktif'])->name('pengguna.bulk-aktif');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::post('/pengguna/{pengguna}/toggle-aktif', [PenggunaController::class, 'toggleAktif'])->name('pengguna.toggle-aktif');

        // Tetapan
        Route::get('/tetapan', [TetapanController::class, 'index'])->name('tetapan.index');
        Route::post('/tetapan', [TetapanController::class, 'update'])->name('tetapan.update');
    });
});
