<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim($request->input('cari', ''));

        $queryAktif     = User::where('aktif', true)->orderBy('name');
        $queryNyahaktif = User::where('aktif', false)->orderBy('name');

        if ($cari !== '') {
            $filter = function ($q) use ($cari) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%")
                  ->orWhere('jabatan', 'like', "%{$cari}%");
            };
            $queryAktif->where($filter);
            $queryNyahaktif->where($filter);
        }

        $penggunaAktif     = $queryAktif->paginate(25, ['*'], 'page_aktif')->appends(['cari' => $cari]);
        $penggunaNyahaktif = $queryNyahaktif->paginate(25, ['*'], 'page_nyahaktif')->appends(['cari' => $cari]);

        return view('pengguna.index', compact('penggunaAktif', 'penggunaNyahaktif', 'cari'));
    }

    public function store(StorePenggunaRequest $request)
    {
        $validated = $request->validated();

        $pengguna = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'jabatan'  => $validated['jabatan'] ?? null,
            'peranan'  => $validated['peranan'],
            'password' => Hash::make($validated['password']),
            'aktif'    => true,
        ]);

        AuditLogger::catat('tambah_pengguna', $pengguna, [
            'email'   => $pengguna->email,
            'peranan' => $pengguna->peranan,
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna baru berjaya ditambah.');
    }

    public function update(UpdatePenggunaRequest $request, User $pengguna)
    {
        $validated = $request->validated();

        // Lindungi — pentadbir tidak boleh nyahaktifkan akaun sendiri
        if ($pengguna->id === auth()->id() && isset($validated['aktif']) && !$validated['aktif']) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan akaun anda sendiri.');
        }

        $pengguna->update($validated);

        AuditLogger::catat('kemaskini_pengguna', $pengguna, [
            'peranan' => $pengguna->peranan,
            'aktif'   => $pengguna->aktif,
        ]);

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

        AuditLogger::catat('reset_kata_laluan', $pengguna);

        return back()->with('success', 'Kata laluan pengguna berjaya ditukar semula.');
    }

    // ── Toggle aktif/nyahaktif satu pengguna ──
    public function toggleAktif(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan akaun anda sendiri.');
        }

        $pengguna->update(['aktif' => !$pengguna->aktif]);

        $kodTindakan = $pengguna->aktif ? 'aktifkan_pengguna' : 'nyahaktifkan_pengguna';
        $labelTindakan = $pengguna->aktif ? 'diaktifkan' : 'dinyahaktifkan';
        AuditLogger::catat($kodTindakan, $pengguna);

        return back()->with('success', "Akaun {$pengguna->name} berjaya {$labelTindakan}.");
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

        $nilaiAktif    = ($tindakan === 'aktifkan') ? true : false;
        $jumlah        = User::whereIn('id', $ids)->count();
        $kodTindakan   = $nilaiAktif ? 'bulk_aktifkan' : 'bulk_nyahaktifkan';

        User::whereIn('id', $ids)->update(['aktif' => $nilaiAktif]);

        AuditLogger::catat($kodTindakan, null, ['jumlah' => $jumlah, 'ids' => array_values($ids)]);

        $label = $nilaiAktif ? 'diaktifkan' : 'dinyahaktifkan';

        return back()->with('success', "{$jumlah} pengguna berjaya {$label}.");
    }
}
