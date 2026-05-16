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

        \Illuminate\Support\Facades\Log::info(
            "Kata laluan ditukar semula untuk pengguna ID:{$pengguna->id} oleh pentadbir ID:" . auth()->id()
        );

        return back()->with('success', 'Kata laluan pengguna berjaya ditukar semula.');
    }

    // ── Toggle aktif/nyahaktif satu pengguna ──
    public function toggleAktif(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan akaun anda sendiri.');
        }

        $pengguna->update(['aktif' => !$pengguna->aktif]);

        $tindakan = $pengguna->aktif ? 'diaktifkan' : 'dinyahaktifkan';

        return back()->with('success', "Akaun {$pengguna->name} berjaya {$tindakan}.");
    }

    // ── Tindakan pukal: aktif/nyahaktif senarai pengguna ──
    public function bulkAktif(Request $request)
    {
        $request->validate([
            'ids'      => 'required|array|min:1',
            'ids.*'    => 'integer|exists:users,id',
            'tindakan' => 'required|in:aktifkan,nyahaktifkan',
        ]);

        $ids      = $request->ids;
        $tindakan = $request->tindakan;

        // Keluarkan ID pentadbir semasa jika tindakan nyahaktifkan
        if ($tindakan === 'nyahaktifkan') {
            $ids = array_filter($ids, fn($id) => $id !== auth()->id());
        }

        if (empty($ids)) {
            return back()->with('error', 'Tiada pengguna yang boleh diproses — anda tidak boleh menyahaktifkan akaun sendiri.');
        }

        $nilaiAktif = ($tindakan === 'aktifkan') ? true : false;
        $jumlah     = User::whereIn('id', $ids)->count();

        User::whereIn('id', $ids)->update(['aktif' => $nilaiAktif]);

        $label = $nilaiAktif ? 'diaktifkan' : 'dinyahaktifkan';

        return back()->with('success', "{$jumlah} pengguna berjaya {$label}.");
    }
}
