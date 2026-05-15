<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Maksimum percubaan sebelum dikunci
    protected int $maxAttempts = 5;
    // Tempoh kunci (minit)
    protected int $decayMinutes = 15;

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
                return back()->withErrors([
                    'email' => 'Akaun anda telah dinyahaktifkan. Sila hubungi pentadbir.',
                ]);
            }

            // Log masuk berjaya — buang rekod rate limit
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Log masuk gagal — tambah kiraan
        RateLimiter::hit($throttleKey, $this->decayMinutes * 60);

        $remaining = RateLimiter::remaining($throttleKey, $this->maxAttempts);
        $msg = 'Emel atau kata laluan tidak tepat.';
        if ($remaining <= 2) {
            $msg .= " ({$remaining} percubaan berbaki sebelum akaun dikunci sementara)";
        }

        return back()->withErrors(['email' => $msg])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
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
