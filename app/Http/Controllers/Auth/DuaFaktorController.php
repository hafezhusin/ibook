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

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\KodOTP;
use App\Models\SesiAktif;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class DuaFaktorController extends Controller
{
    /** Maksimum percubaan OTP sebelum sesi dibatalkan */
    protected int $maxPercubaan = 3;

    /**
     * Paparkan borang input kod OTP.
     */
    public function show(Request $request)
    {
        if (! session('2fa_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('2fa_user_id'));
        // @phpstan-ignore-next-line nullsafe.neverNull — find() boleh pulang null jika ID tidak sah
        $emailSembunyi = $user?->masked_email ?? '***@***';

        return view('auth.dua-faktor', compact('emailSembunyi'));
    }

    /**
     * Sahkan kod OTP yang dimasukkan pengguna.
     *
     * Keselamatan:
     *  - OTP disimpan sebagai HMAC-SHA256, dibandingkan dengan hash_equals() (tahan timing attack)
     *  - expires_at disimpan eksplisit supaya TTL tidak dapat dilanjut dengan percubaan berulang
     *  - Percubaan gagal, lockout, dan OTP luput dicatat dalam audit log
     */
    public function verify(Request $request)
    {
        $request->validate([
            'kod' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ], [
            'kod.required' => 'Sila masukkan kod pengesahan.',
            'kod.size' => 'Kod mestilah 6 digit.',
            'kod.regex' => 'Kod mestilah 6 digit angka.',
        ]);

        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Sesi telah tamat. Sila log masuk semula.']);
        }

        $cacheKey = '2fa_otp_'.$userId;
        $cached = Cache::get($cacheKey);

        // ── Semak cache kosong ATAU expiry eksplisit sudah berlalu ────
        // expires_at disemak secara manual supaya percubaan gagal tidak
        // dapat melanjutkan hayat OTP melalui Cache::put() semula.
        if (! $cached || now()->timestamp > ($cached['expires_at'] ?? 0)) {
            Cache::forget($cacheKey);
            session()->forget(['2fa_user_id', '2fa_remember']);
            AuditLogger::catat('2fa_otp_luput', null, [
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')
                ->withErrors(['email' => 'Kod pengesahan telah tamat tempoh. Sila log masuk semula.']);
        }

        // ── Bandingkan OTP menggunakan hash_equals() — tahan timing attack ──
        $inputHash = hash_hmac('sha256', $request->kod, config('app.key'));
        $simpanHash = $cached['kod_hash'] ?? '';

        if (! hash_equals($simpanHash, $inputHash)) {
            $cached['percubaan']++;

            if ($cached['percubaan'] >= $this->maxPercubaan) {
                Cache::forget($cacheKey);
                session()->forget(['2fa_user_id', '2fa_remember']);
                AuditLogger::catat('2fa_lockout', null, [
                    'user_id' => $userId,
                    'ip' => $request->ip(),
                    'percubaan' => $cached['percubaan'],
                ]);

                return redirect()->route('login')
                    ->withErrors(['email' => 'Terlalu banyak percubaan. Sila log masuk semula.']);
            }

            // Kemaskini percubaan TANPA melanjut TTL — guna baki masa dari expires_at asal
            $ttlBaki = max(1, ($cached['expires_at'] ?? now()->timestamp) - now()->timestamp);
            Cache::put($cacheKey, $cached, $ttlBaki);

            $berbaki = $this->maxPercubaan - $cached['percubaan'];
            AuditLogger::catat('2fa_otp_salah', null, [
                'user_id' => $userId,
                'ip' => $request->ip(),
                'percubaan' => $cached['percubaan'],
                'berbaki' => $berbaki,
            ]);

            return back()->withErrors([
                'kod' => "Kod tidak sah. {$berbaki} percubaan berbaki.",
            ]);
        }

        // ── Kod betul — selesaikan log masuk ─────────────────────────
        Cache::forget($cacheKey);
        $remember = session('2fa_remember', false);
        session()->forget(['2fa_user_id', '2fa_remember']);

        $user = User::findOrFail($userId);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // Track sesi aktif
        try {
            SesiAktif::where('pengguna_id', $user->id)
                ->where('session_id', $request->session()->getId())
                ->delete();
            SesiAktif::create([
                'pengguna_id'       => $user->id,
                'session_id'        => $request->session()->getId(),
                'ip_address'        => $request->ip(),
                'user_agent'        => substr($request->userAgent() ?? '', 0, 500),
                'kaedah'            => '2fa',
                'log_masuk_pada'    => now(),
                'aktiviti_terakhir' => now(),
            ]);
        } catch (\Throwable) {}

        AuditLogger::catat('log_masuk_berjaya', null, [
            'kaedah' => '2FA',
            'user_agent' => substr($request->userAgent() ?? '', 0, 200),
        ], $user->name.' log masuk ke sistem (2FA)');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Hantar semula kod OTP baharu.
     * Had kadar: 1 permintaan per minit per pengguna.
     */
    public function hantarSemula(Request $request)
    {
        $userId = session('2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $throttleKey = '2fa_resend_'.$userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $saat = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'kod' => "Sila tunggu {$saat} saat sebelum meminta kod baru.",
            ]);
        }

        try {
            $user = User::findOrFail($userId);
            $otp = self::janaOtp();
            $expiry = now()->addMinutes(10);

            Cache::put('2fa_otp_'.$userId, [
                'kod_hash' => hash_hmac('sha256', $otp, config('app.key')),
                'percubaan' => 0,
                'expires_at' => $expiry->timestamp,
            ], $expiry);

            Mail::to($user->email)->send(new KodOTP($user->name, $otp));
            RateLimiter::hit($throttleKey, 60);

            AuditLogger::catat('2fa_resend_otp', null, [
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('DuaFaktorController::hantarSemula gagal: '.$e->getMessage());

            return back()->withErrors(['kod' => 'Gagal menghantar kod. Sila cuba lagi.']);
        }

        return back()->with('success_otp', 'Kod baru telah dihantar ke emel anda.');
    }

    /**
     * Jana kod OTP 6 digit berformat sifar hadapan.
     */
    public static function janaOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
