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
use App\Models\Bahagian;
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
        $cari       = trim($request->input('cari', ''));
        $unit       = trim($request->input('unit', ''));
        $bahagianId = trim($request->input('bahagian_id', ''));

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

        if ($unit !== '') {
            $queryAktif->where('jabatan', $unit);
            $queryPending->where('jabatan', $unit);
            $queryNyahaktif->where('jabatan', $unit);
        }

        if ($bahagianId !== '') {
            $queryAktif->where('bahagian_id', $bahagianId);
            $queryPending->where('bahagian_id', $bahagianId);
            $queryNyahaktif->where('bahagian_id', $bahagianId);
        }

        $appends = array_filter(
            ['cari' => $cari, 'unit' => $unit, 'bahagian_id' => $bahagianId],
            fn ($v) => $v !== ''
        );

        $penggunaAktif    = $queryAktif->paginate(25, ['*'], 'page_aktif')->appends($appends);
        $penggunaPending   = $queryPending->paginate(25, ['*'], 'page_pending')->appends($appends);
        $penggunaNyahaktif = $queryNyahaktif->paginate(25, ['*'], 'page_nyahaktif')->appends($appends);

        // Unit list — dinamik berdasarkan bahagian dipilih (bukan constant statik)
        $unitQuery = User::whereNotNull('jabatan')->where('jabatan', '!=', '');
        if ($bahagianId !== '') {
            $unitQuery->where('bahagian_id', $bahagianId);
        }
        $units = $unitQuery->distinct()->orderBy('jabatan')->pluck('jabatan')->toArray();

        $bahagianList = Bahagian::where('aktif', true)->orderBy('kod')->get();

        return view('pengguna.index', compact(
            'penggunaAktif', 'penggunaPending', 'penggunaNyahaktif',
            'cari', 'unit', 'units', 'bahagianId', 'bahagianList'
        ));
    }

    public function store(StorePenggunaRequest $request)
    {
        $validated = $request->validated();

        $pengguna = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'jabatan' => $validated['jabatan'] ?? null,
            'peranan' => $validated['peranan'],
            'password' => Hash::make($validated['password']),
            'aktif' => true,
        ]);

        AuditLogger::catat('tambah_pengguna', $pengguna, [
            'email' => $pengguna->email,
            'peranan' => $pengguna->peranan,
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna baru berjaya ditambah.');
    }

    public function update(UpdatePenggunaRequest $request, User $pengguna)
    {
        $validated = $request->validated();

        // Lindungi — pentadbir tidak boleh nyahaktifkan akaun sendiri
        if ($pengguna->id === auth()->id() && isset($validated['aktif']) && ! $validated['aktif']) {
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
            'aktif' => $pengguna->aktif,
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
            'password.required' => 'Sila masukkan kata laluan baru.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
            'sebab.required' => 'Sila berikan sebab penukaran kata laluan.',
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

    // ── Import CSV ──────────────────────────────────────────────────

    /**
     * Borang muat naik CSV.
     */
    public function importCsvForm()
    {
        $user = auth()->user();
        $bahagian = $user->isPentadbir()
            ? Bahagian::where('aktif', true)->orderBy('kod')->get()
            : Bahagian::where('id', $user->bahagian_id)->get();

        $pratonton   = session('import_pratonton');
        $bahagianId  = session('import_bahagian_id');

        return view('pengguna.import-csv', compact('bahagian', 'pratonton', 'bahagianId'));
    }

    /**
     * Parse CSV → simpan dalam session → tunjuk pratonton.
     */
    public function importCsvPratonton(Request $request)
    {
        $authUser = auth()->user();

        $request->validate([
            'csv_fail'    => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'bahagian_id' => ['required', 'integer', 'exists:bahagian,id'],
        ], [
            'csv_fail.required'    => 'Sila muat naik fail CSV.',
            'csv_fail.mimes'       => 'Fail mestilah dalam format CSV (.csv).',
            'csv_fail.max'         => 'Saiz fail tidak boleh melebihi 2MB.',
            'bahagian_id.required' => 'Sila pilih bahagian.',
            'bahagian_id.exists'   => 'Bahagian tidak sah.',
        ]);

        $bahagianId = (int) $request->bahagian_id;

        // Urus setia hanya boleh import untuk bahagian sendiri
        if ($authUser->isUrusSetia() && $authUser->bahagian_id !== $bahagianId) {
            return back()->with('error', 'Anda hanya boleh mengimport pengguna untuk bahagian anda sendiri.');
        }

        // ── Parse CSV ──
        $handle = fopen($request->file('csv_fail')->getPathname(), 'r');

        // Buang BOM (Excel CSV)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'Fail CSV kosong atau tidak sah.');
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        foreach (['nama', 'emel'] as $wajib) {
            if (! in_array($wajib, $header)) {
                fclose($handle);
                return back()->with('error', "Lajur wajib '{$wajib}' tidak dijumpai dalam CSV. Sila guna templat yang disediakan.");
            }
        }

        $baris   = [];
        $bilBaris = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $bilBaris++;
            if ($bilBaris > 500) break;
            if (count($row) < count($header)) continue;

            $data = array_combine($header, array_slice($row, 0, count($header)));

            $nama    = trim($data['nama'] ?? '');
            $emel    = strtolower(trim($data['emel'] ?? ''));
            $unit    = trim($data['unit'] ?? $data['jabatan'] ?? '');
            $peranan = trim($data['peranan'] ?? 'staf');

            if (empty($nama) || empty($emel)) {
                continue;
            }

            if (! filter_var($emel, FILTER_VALIDATE_EMAIL)) {
                $baris[] = [
                    'baris'       => $bilBaris,
                    'nama'        => $nama,
                    'emel'        => $emel,
                    'unit'        => $unit,
                    'peranan'     => $peranan,
                    'status'      => 'ralat',
                    'mesej'       => 'Format emel tidak sah',
                    'existing_id' => null,
                ];
                continue;
            }

            $peranan = in_array($peranan, ['staf', 'urus_setia', 'pentadbir_sistem'])
                ? $peranan : 'staf';

            $existing = User::where('email', $emel)->first();

            if ($existing) {
                $status = $existing->aktif ? 'aktif' : 'tidak_aktif';
                $mesej  = $existing->aktif ? 'Sudah aktif dalam sistem' : 'Akan diaktifkan semula';
            } else {
                $status = 'baru';
                $mesej  = 'Pengguna baru akan dicipta';
            }

            $baris[] = [
                'baris'       => $bilBaris,
                'nama'        => $nama,
                'emel'        => $emel,
                'unit'        => $unit,
                'peranan'     => $peranan,
                'status'      => $status,
                'mesej'       => $mesej,
                'existing_id' => $existing?->id,
            ];
        }
        fclose($handle);

        if (empty($baris)) {
            return back()->with('error', 'Tiada data yang sah dalam CSV. Semak format dan cuba semula.');
        }

        session(['import_pratonton' => $baris, 'import_bahagian_id' => $bahagianId]);

        return redirect()->route('pengguna.import-csv');
    }

    /**
     * Proses baris yang dipilih daripada pratonton.
     */
    public function importCsvProses(Request $request)
    {
        $pratonton  = session('import_pratonton');
        $bahagianId = session('import_bahagian_id');

        if (! $pratonton || ! $bahagianId) {
            return redirect()->route('pengguna.import-csv')
                ->with('error', 'Sesi pratonton tamat. Sila muat naik CSV semula.');
        }

        $request->validate([
            'pilihan'   => ['required', 'array', 'min:1'],
            'pilihan.*' => ['integer'],
        ], [
            'pilihan.required' => 'Sila pilih sekurang-kurangnya satu pengguna untuk diproses.',
        ]);

        $dipilih    = array_map('intval', $request->pilihan);
        $kataLaluan = 'iBook@' . date('Y');
        $dicipta    = 0;
        $diaktifkan = 0;

        foreach ($pratonton as $i => $b) {
            if (! in_array($i, $dipilih)) {
                continue;
            }
            if (in_array($b['status'], ['ralat', 'aktif'])) {
                continue;
            }

            if ($b['status'] === 'baru') {
                User::create([
                    'name'        => $b['nama'],
                    'email'       => $b['emel'],
                    'jabatan'     => $b['unit'] ?: null,
                    'peranan'     => $b['peranan'],
                    'password'    => Hash::make($kataLaluan),
                    'aktif'       => true,
                    'bahagian_id' => $bahagianId,
                ]);
                $dicipta++;
            } elseif ($b['status'] === 'tidak_aktif' && $b['existing_id']) {
                User::where('id', $b['existing_id'])->update([
                    'aktif'       => true,
                    'bahagian_id' => $bahagianId,
                    'jabatan'     => $b['unit'] ?: null,
                ]);
                $diaktifkan++;
            }
        }

        session()->forget(['import_pratonton', 'import_bahagian_id']);

        $jumlah = $dicipta + $diaktifkan;
        AuditLogger::catat('import_csv_pengguna', null, [
            'bahagian_id' => $bahagianId,
            'dicipta'     => $dicipta,
            'diaktifkan'  => $diaktifkan,
            'jumlah'      => $jumlah,
        ]);

        $mesej = "{$jumlah} pengguna berjaya diproses ({$dicipta} dicipta, {$diaktifkan} diaktifkan semula).";
        if ($dicipta > 0) {
            $mesej .= " Kata laluan lalai bagi pengguna baru: <code>{$kataLaluan}</code>";
        }

        return redirect()->route('pengguna.index')->with('success_html', $mesej);
    }

    /**
     * Muat turun templat CSV.
     */
    public function downloadTemplat()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="templat-import-pengguna.csv"',
        ];

        $callback = function () {
            $h = fopen('php://output', 'w');
            fprintf($h, "\xEF\xBB\xBF"); // BOM — supaya Excel buka dengan betul
            fputcsv($h, ['nama', 'emel', 'unit', 'peranan']);
            fputcsv($h, ['Ahmad bin Ali', 'ahmad.ali@jabatan.gov.my', 'Unit Gaji', 'staf']);
            fputcsv($h, ['Siti Nurhaliza binti Hassan', 'siti.hassan@jabatan.gov.my', 'Unit Bayaran', 'staf']);
            fputcsv($h, ['Mohd Razif bin Roslan', 'razif.roslan@jabatan.gov.my', 'Unit ICT', 'urus_setia']);
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
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

        $pengguna->update(['aktif' => ! $pengguna->aktif]);

        // Paksa log keluar sesi aktif pengguna (terutama bila dinyahaktifkan)
        Cache::put("paksa_log_keluar_{$pengguna->id}", true, now()->addHours(24));

        $kodTindakan = $pengguna->aktif ? 'aktifkan_pengguna' : 'nyahaktifkan_pengguna';
        $labelTindakan = $pengguna->aktif ? 'diaktifkan' : 'dinyahaktifkan';
        $butiran = $sebab !== '' ? ['sebab' => $sebab] : [];
        AuditLogger::catat($kodTindakan, $pengguna, $butiran);

        return back()->with('success', "Akaun {$pengguna->name} berjaya {$labelTindakan}.");
    }

    // ── Tindakan pukal: aktif/nyahaktif senarai pengguna ──
    public function bulkAktif(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
            'tindakan' => 'required|in:aktifkan,nyahaktifkan',
        ]);

        $ids = $request->ids;
        $tindakan = $request->tindakan;

        // Keluarkan ID pentadbir semasa jika tindakan nyahaktifkan
        if ($tindakan === 'nyahaktifkan') {
            $ids = array_filter($ids, fn ($id) => $id !== auth()->id());
        }

        if (empty($ids)) {
            return back()->with('error', 'Tiada pengguna yang boleh diproses — anda tidak boleh menyahaktifkan akaun sendiri.');
        }

        $nilaiAktif = ($tindakan === 'aktifkan') ? true : false;
        $jumlah = User::whereIn('id', $ids)->count();
        $kodTindakan = $nilaiAktif ? 'bulk_aktifkan' : 'bulk_nyahaktifkan';

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
