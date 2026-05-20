<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // ── Form lupa kata laluan ──────────────────────────────────────────
    public function showLinkForm()
    {
        return view('auth.lupa-kata-laluan');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(
            ['email' => 'required|email'],
            [
                'email.required' => 'Sila masukkan emel anda.',
                'email.email'    => 'Format emel tidak sah.',
            ]
        );

        // Always return the same message to prevent user enumeration
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Jika emel anda berdaftar dalam sistem, anda akan menerima pautan set semula kata laluan tidak lama lagi. Sila semak folder Spam jika tidak menerima e-mel.');
    }

    // ── Form set semula kata laluan ────────────────────────────────────
    public function showResetForm(string $token, Request $request)
    {
        return view('auth.reset-kata-laluan', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => ['required', 'confirmed', Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ], [
            'token.required'    => 'Token tidak sah.',
            'email.required'    => 'Sila masukkan emel.',
            'password.required' => 'Sila masukkan kata laluan baharu.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'password.min'      => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success_reset', 'Kata laluan berjaya ditetapkan semula. Sila log masuk dengan kata laluan baharu anda.');
        }

        return back()->withErrors(['email' => match ($status) {
            Password::INVALID_TOKEN   => 'Pautan ini telah tamat tempoh atau tidak sah. Sila mohon pautan baharu.',
            Password::INVALID_USER    => 'Emel tidak dijumpai dalam sistem.',
            Password::RESET_THROTTLED => 'Terlalu banyak percubaan. Sila cuba sebentar lagi.',
            default                   => 'Ralat berlaku. Sila cuba semula.',
        }]);
    }
}
