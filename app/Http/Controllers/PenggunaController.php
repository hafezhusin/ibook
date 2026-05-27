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

use App\Http\Requests\StorePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim($request->input('cari', ''));

        // Aktif
        $queryAktif = User::where('aktif', true)->orderBy('name');

        // Menunggu Kelulusan — SSO baru, belum pernah log masuk
        $queryPending = User::where('aktif', false)
                            ->whereNull('last_login_at')
                            ->orderByDesc('created_at');

        // Dinyahaktifkan — pernah aktif, kemudian dinyahaktifkan
        $queryNyahaktif = User::where('aktif', false)
                              ->whereNotNull('last_login_at')
                              ->orderBy('name');

        if ($cari !== '') {
            $filter = function ($q) use ($cari) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%")
                  ->orWhere('jabatan', 'like', "%{$cari}%");
            };
            $queryAktif->where($filter);
            $queryPending->where($filter);
            $queryNyahaktif->where($filter);
        }

        $penggunaAktif     = $queryAktif->paginate(25, ['*'], 'page_aktif')->appends(['cari' => $cari]);
        $penggunaPending   = $queryPending->paginate(25, ['*'], 'page_pending')->appends(['cari' => $cari]);
        $penggunaNyahaktif = $queryNyahaktif->paginate(25, ['*'], 'page_nyahaktif')->appends(['cari' => $cari]);

        return view('pengguna.index', compact('penggunaAktif', 'penggunaPending', 'penggunaNyahaktif', 'cari'));
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

        // Nama dari MyGovUC — semua akaun @anm.gov.my nama tidak boleh diubah admin
        if ($pengguna->google_id || str_ends_with($pengguna->email, '@anm.gov.my')) {
            unset($validated['name']);
        }

        $pengguna->update($validated);

        // Paksa log keluar sesi aktif jika peranan atau status aktif berubah
        if ($pengguna->wasChanged(['peranan', 'aktif'])) {
            Cache::put("paksa_log_keluar_{$pengguna->id}", true, now()->addHours(24));
        }

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
            'sebab' => 'required|string|max:255',
        ], [
            'password.required'  => 'Sila masukkan kata laluan baru.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'password.min'       => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
            'sebab.required'     => 'Sila berikan sebab penukaran kata laluan.',
        ]);

        $pengguna->update([
            'password' => Hash::make($request->password),
        ]);

        // Paksa log keluar sesi aktif pengguna selepas reset kata laluan
        Cache::put("paksa_log_keluar_{$pengguna->id}", true, now()->addHours(24));

        AuditLogger::catat('reset_kata_laluan', $pengguna, [
            'sebab' => $request->sebab,
        ]);

        return back()->with('success', 'Kata laluan pengguna berjaya ditukar semula.');
    }

    // ── Toggle aktif/nyahaktif satu pengguna ──
    public function toggleAktif(Request $request, User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Anda tidak boleh menyahaktifkan akaun anda sendiri.');
        }

        // Jika sedang nyahaktifkan, sebab wajib diisi
        $sebab = '';
        if ($pengguna->aktif) {
            $request->validate([
                'sebab' => 'required|string|max:255',
            ], [
                'sebab.required' => 'Sila berikan sebab nyahaktifkan akaun ini.',
            ]);
            $sebab = $request->sebab;
        }

        $pengguna->update(['aktif' => !$pengguna->aktif]);

        // Paksa log keluar sesi aktif pengguna (terutama bila dinyahaktifkan)
        Cache::put("paksa_log_keluar_{$pengguna->id}", true, now()->addHours(24));

        $kodTindakan   = $pengguna->aktif ? 'aktifkan_pengguna' : 'nyahaktifkan_pengguna';
        $labelTindakan = $pengguna->aktif ? 'diaktifkan' : 'dinyahaktifkan';
        $butiran       = $sebab !== '' ? ['sebab' => $sebab] : [];
        AuditLogger::catat($kodTindakan, $pengguna, $butiran);

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

        // Paksa log keluar sesi aktif semua pengguna yang terkesan
        foreach ($ids as $id) {
            Cache::put("paksa_log_keluar_{$id}", true, now()->addHours(24));
        }

        AuditLogger::catat($kodTindakan, null, ['jumlah' => $jumlah, 'ids' => array_values($ids)]);

        $label = $nilaiAktif ? 'diaktifkan' : 'dinyahaktifkan';

        return back()->with('success', "{$jumlah} pengguna berjaya {$label}.");
    }
}
