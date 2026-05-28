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

namespace App\Http\Controllers;

use App\Models\User;
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
        $units = User::SENARAI_UNIT;

        return view('profil.index', compact('user', 'units'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $isSsoUser = $user->google_id || str_ends_with($user->email, '@anm.gov.my');

        // Nama SSO diambil dari MyGovUC — tidak boleh diubah sendiri
        $rehat = [];
        if (! $isSsoUser) {
            $rehat['name'] = 'required|string|max:255';
        }
        if (! $user->isStaf()) {
            $rehat['jabatan'] = ['nullable', 'string', 'in:'.implode(',', User::SENARAI_UNIT)];
        }

        $validated = $request->validate($rehat, [
            'name.required' => 'Sila masukkan nama penuh anda.',
            'jabatan.in' => 'Sila pilih unit yang sah dari senarai.',
        ]);

        // Cegah bypass — buang nama jika SSO, buang jabatan jika staf
        if ($isSsoUser) {
            unset($validated['name']);
        }
        if ($user->isStaf()) {
            unset($validated['jabatan']);
        }

        $user->update($validated);
        AuditLogger::catat('kemaskini_profil', $user);

        return back()->with('success', 'Maklumat profil berjaya dikemaskini.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Akaun MyGovUC tidak menggunakan kata laluan sistem
        if ($user->google_id || str_ends_with($user->email, '@anm.gov.my')) {
            return back()->with('error_password',
                'Akaun @anm.gov.my diuruskan oleh MyGovUC. Kata laluan tidak boleh ditukar di sini.');
        }

        $request->validate([
            'kata_laluan_semasa' => 'required|string',
            'password' => [
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
            'password.required' => 'Sila masukkan kata laluan baharu.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
        ]);

        // Semak kata laluan semasa
        if (! Hash::check($request->kata_laluan_semasa, $user->password)) {
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

    /**
     * Toggle pengesahan dua faktor (2FA) untuk pengguna semasa.
     */
    public function toggle2fa(Request $request)
    {
        $user = Auth::user();
        $aktif = ! $user->dua_faktor_aktif;

        $user->update(['dua_faktor_aktif' => $aktif]);

        AuditLogger::catat(
            $aktif ? 'aktifkan_2fa' : 'nyahaktifkan_2fa',
            $user,
            [],
            $user->name.($aktif ? ' mengaktifkan' : ' menyahaktifkan').' pengesahan dua faktor'
        );

        $mesej = $aktif
            ? 'Pengesahan dua faktor (2FA) berjaya diaktifkan. Kod akan dihantar ke emel anda setiap kali log masuk.'
            : 'Pengesahan dua faktor (2FA) berjaya dinyahaktifkan.';

        return back()->with('success', $mesej);
    }
}
