<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = User::orderBy('name')->get();
        return view('pengguna.index', compact('pengguna'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email|max:255',
            'jabatan' => 'nullable|string|max:255',
            'peranan' => 'required|in:pentadbir_sistem,urus_setia,staf',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'name.required'     => 'Sila masukkan nama pengguna.',
            'email.required'    => 'Sila masukkan emel.',
            'email.unique'      => 'Emel ini telah digunakan.',
            'peranan.required'  => 'Sila pilih peranan.',
            'password.required' => 'Sila masukkan kata laluan.',
            'password.confirmed'=> 'Pengesahan kata laluan tidak sepadan.',
            'password.min'      => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'jabatan'  => $validated['jabatan'] ?? null,
            'peranan'  => $validated['peranan'],
            'password' => Hash::make($validated['password']),
            'aktif'    => true,
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna baru berjaya ditambah.');
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'peranan' => 'required|in:pentadbir_sistem,urus_setia,staf',
            'aktif'   => 'boolean',
        ]);

        // Lindungi — pentadbir tidak boleh nyahaktifkan akaun sendiri
        if ($pengguna->id === auth()->id() && isset($validated['aktif']) && !$validated['aktif']) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan akaun anda sendiri.');
        }

        $pengguna->update($validated);

        return redirect()->route('pengguna.index')
            ->with('success', 'Maklumat pengguna berjaya dikemaskini.');
    }

    public function resetPassword(Request $request, User $pengguna)
    {
        $request->validate([
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
            'password.required'  => 'Sila masukkan kata laluan baru.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'password.min'       => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
        ]);

        $pengguna->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate all existing sessions for this user (security best practice)
        // This requires session driver to support user sessions
        // For file driver, we log a note — in production use database sessions
        \Illuminate\Support\Facades\Log::info(
            "Kata laluan ditukar semula untuk pengguna ID:{$pengguna->id} oleh pentadbir ID:" . auth()->id()
        );

        return back()->with('success', 'Kata laluan pengguna berjaya ditukar semula.');
    }
}
