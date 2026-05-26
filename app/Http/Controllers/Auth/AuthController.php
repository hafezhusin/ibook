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

use App\Enums\PerananPengguna;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\DuaFaktorController;
use App\Mail\AmaranKeselamatan;
use App\Mail\KodOTP;
use App\Models\ActivityLog;
use App\Models\Tetapan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Maksimum percubaan sebelum dikunci
    protected int $maxAttempts = 5;
    // Tempoh kunci (minit)
    protected int $decayMinutes = 15;

    /**
     * Redirect pengguna ke Google untuk pengesahan OAuth.
     */
    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Terima callback dari Google selepas pengesahan berjaya.
     * - Hanya domain @anm.gov.my dibenarkan
     * - Match akaun sedia ada via email, atau cipta akaun baharu (Staf)
     * - Pengguna Google terus log masuk — tiada 2FA (Google sudah uruskan)
     */
    public function callbackGoogle(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google SSO callback gagal: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Log masuk Google gagal. Sila cuba lagi.');
        }

        // ── Semak domain @anm.gov.my ──────────────────────────────────
        $domain = Str::after($googleUser->email, '@');
        if ($domain !== 'anm.gov.my') {
            return redirect()->route('login')
                ->with('error', 'Hanya akaun rasmi @anm.gov.my dibenarkan log masuk melalui Google.');
        }
        // ─────────────────────────────────────────────────────────────

        // ── Cari atau cipta pengguna ──────────────────────────────────
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            // Kemas kini google_id jika belum ada (akaun lama sebelum SSO)
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }

            // Semak akaun aktif
            if (!$user->aktif) {
                return redirect()->route('login')
                    ->with('error', 'Akaun anda telah dinyahaktifkan. Sila hubungi pentadbir.');
            }
        } else {
            // Cipta akaun baharu dengan peranan Staf
            $user = User::create([
                'name'      => $googleUser->name,
                'email'     => $googleUser->email,
                'google_id' => $googleUser->id,
                'password'  => Hash::make(Str::random(32)),
                'peranan'   => PerananPengguna::Staf->value,
                'aktif'     => true,
            ]);
        }
        // ─────────────────────────────────────────────────────────────

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        AuditLogger::catat('log_masuk_google', null, [
            'user_agent' => substr($request->userAgent() ?? '', 0, 200),
        ], $user->name . ' log masuk melalui Google SSO');

        return redirect()->intended(route('dashboard'));
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Sila masukkan emel.',
            'email.email'       => 'Format emel tidak sah.',
            'password.required' => 'Sila masukkan kata laluan.',
        ]);

        // ── Semak rate limit ──────────────────────────────────────────
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minit   = ceil($seconds / 60);
            return back()->withErrors([
                'email' => "Terlalu banyak percubaan log masuk. Cuba lagi dalam {$minit} minit.",
            ])->onlyInput('email');
        }
        // ─────────────────────────────────────────────────────────────

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            if (!Auth::user()->aktif) {
                Auth::logout();
                RateLimiter::hit($throttleKey, $this->decayMinutes * 60);

                // Audit: percubaan log masuk akaun dinyahaktifkan
                AuditLogger::catat('percubaan_akaun_nyahaktif', null, [
                    'email_dicuba' => $request->email,
                    'user_agent'   => substr($request->userAgent() ?? '', 0, 200),
                ], "Percubaan log masuk akaun dinyahaktif — {$request->email}");

                return back()->withErrors([
                    'email' => 'Akaun anda telah dinyahaktifkan. Sila hubungi pentadbir.',
                ]);
            }

            // Log masuk berjaya — buang rekod rate limit
            RateLimiter::clear($throttleKey);

            // ── Semak 2FA ──────────────────────────────────────────────
            if (Auth::user()->dua_faktor_aktif) {
                $user = Auth::user();

                // Log keluar sementara — log masuk penuh hanya selepas OTP disahkan
                Auth::logout();

                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_remember', $remember);

                // Jana dan hantar OTP (kegagalan hantar tidak halang navigasi ke halaman OTP)
                try {
                    $otp = DuaFaktorController::janaOtp();
                    Cache::put('2fa_otp_' . $user->id, [
                        'kod'       => $otp,
                        'percubaan' => 0,
                    ], now()->addMinutes(10));
                    Mail::to($user->email)->send(new KodOTP($user->name, $otp));
                } catch (\Throwable $e) {
                    Log::warning('2FA OTP hantar gagal: ' . $e->getMessage());
                }

                return redirect()->route('dua-faktor.show');
            }
            // ────────────────────────────────────────────────────────────

            $request->session()->regenerate();

            // Rekod waktu log masuk terakhir
            Auth::user()->update(['last_login_at' => now()]);

            // Audit: log masuk berjaya
            AuditLogger::catat('log_masuk_berjaya', null, [
                'user_agent' => substr($request->userAgent() ?? '', 0, 200),
            ], Auth::user()->name . ' log masuk ke sistem');

            return redirect()->intended(route('dashboard'));
        }

        // Log masuk gagal — tambah kiraan
        RateLimiter::hit($throttleKey, $this->decayMinutes * 60);

        // Audit: log masuk gagal
        AuditLogger::catat('log_masuk_gagal', null, [
            'email_dicuba' => $request->email,
            'user_agent'   => substr($request->userAgent() ?? '', 0, 200),
        ], "Percubaan log masuk gagal — {$request->email}");

        // Semak ancaman: banyak kegagalan dari IP sama
        $this->semakAncaman($request);

        $remaining = RateLimiter::remaining($throttleKey, $this->maxAttempts);
        $msg = 'Emel atau kata laluan tidak tepat.';
        if ($remaining <= 2) {
            $msg .= " ({$remaining} percubaan berbaki sebelum akaun dikunci sementara)";
        }

        return back()->withErrors(['email' => $msg])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Audit: log keluar (sebelum Auth::logout() supaya pengguna masih ada)
        AuditLogger::catat('log_keluar', null, [
            'user_agent' => substr($request->userAgent() ?? '', 0, 200),
        ], Auth::user()?->name . ' log keluar dari sistem');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Semak corak percubaan log masuk mencurigai dari IP yang sama.
     * Hantar e-mel amaran kepada Urus Setia jika ambang dicapai.
     * Guna cache untuk elak spam e-mel (1 amaran per jam per IP).
     */
    private function semakAncaman(Request $request): void
    {
        $ip       = $request->ip();
        $cacheKey = 'amaran_keselamatan_' . md5($ip);

        // Jika dah hantar amaran dalam 1 jam untuk IP ini, langkau
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $kiraan = ActivityLog::where('tindakan', 'log_masuk_gagal')
                ->where('ip_address', $ip)
                ->where('dicipta_pada', '>=', now()->subHour())
                ->count();

            // Hantar amaran bila capai 10 percubaan gagal dalam 1 jam
            if ($kiraan >= 10) {
                $emelAdmin = Tetapan::get('emel_notifikasi');
                if ($emelAdmin) {
                    // Ambil 5 emel terkini yang dicuba dari IP ini
                    $emelDicuba = ActivityLog::where('tindakan', 'log_masuk_gagal')
                        ->where('ip_address', $ip)
                        ->where('dicipta_pada', '>=', now()->subHour())
                        ->latest('dicipta_pada')
                        ->limit(5)
                        ->pluck('butiran')
                        ->map(fn ($b) => $b['email_dicuba'] ?? '?')
                        ->unique()
                        ->values()
                        ->all();

                    Mail::to($emelAdmin)->send(new AmaranKeselamatan(
                        ip:          $ip,
                        kiraan:      $kiraan,
                        emelDicuba:  $emelDicuba,
                    ));

                    // Jangan hantar lagi dalam 1 jam untuk IP ini
                    Cache::put($cacheKey, true, now()->addHour());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('semakAncaman: ' . $e->getMessage());
        }
    }

    /**
     * Kunci throttle unik berdasarkan emel + IP
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }
}
