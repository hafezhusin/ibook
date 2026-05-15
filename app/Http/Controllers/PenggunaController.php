<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'jabatan' => 'nullable|string|max:255',
            'peranan' => 'required|in:pentadbir_sistem,urus_setia,staf',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Sila masukkan nama pengguna.',
            'email.required' => 'Sila masukkan emel.',
            'email.unique' => 'Emel ini telah digunakan.',
            'peranan.required' => 'Sila pilih peranan.',
            'password.required' => 'Sila masukkan kata laluan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'jabatan' => $validated['jabatan'],
            'peranan' => $validated['peranan'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna baru berjaya ditambah.');
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'peranan' => 'required|in:pentadbir_sistem,urus_setia,staf',
            'aktif' => 'boolean',
        ]);

        $pengguna->update($validated);

        return redirect()->route('pengguna.index')
            ->with('success', 'Maklumat pengguna berjaya dikemaskini.');
    }

    public function resetPassword(Request $request, User $pengguna)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $pengguna->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Kata laluan pengguna berjaya ditukar semula.');
    }
}
