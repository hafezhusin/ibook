<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profil.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Sila masukkan nama penuh anda.',
        ]);

        $user->update($validated);
        AuditLogger::catat('kemaskini_profil', $user);

        return back()->with('success', 'Maklumat profil berjaya dikemaskini.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'kata_laluan_semasa'  => 'required|string',
            'password'            => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'kata_laluan_semasa.required' => 'Sila masukkan kata laluan semasa.',
            'password.required'           => 'Sila masukkan kata laluan baharu.',
            'password.confirmed'          => 'Pengesahan kata laluan tidak sepadan.',
            'password.min'                => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
        ]);

        // Semak kata laluan semasa
        if (!Hash::check($request->kata_laluan_semasa, $user->password)) {
            return back()
                ->withErrors(['kata_laluan_semasa' => 'Kata laluan semasa tidak betul.'])
                ->with('tab', 'password');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLogger::catat('tukar_kata_laluan', $user);

        return back()->with('success_password', 'Kata laluan berjaya ditukar.');
    }
}
