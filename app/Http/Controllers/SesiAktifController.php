<?php

/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 */

namespace App\Http\Controllers;

use App\Models\SesiAktif;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SesiAktifController extends Controller
{
    /**
     * Senarai semua sesi log masuk aktif — untuk pentadbir sistem sahaja.
     */
    public function index()
    {
        abort_unless(auth()->user()->isPentadbir(), 403, 'Akses terhad kepada Pentadbir Sistem sahaja.');

        // Bersihkan sesi lapuk melebihi hayat sesi
        $hayatMenit = config('session.lifetime', 60);
        SesiAktif::bersihStale($hayatMenit);

        $sesiAktif = SesiAktif::with('pengguna:id,name,email,peranan,jabatan,aktif')
            ->orderByDesc('aktiviti_terakhir')
            ->get();

        $sesiSendiri  = request()->session()->getId();
        $jumlahSesi   = $sesiAktif->count();
        $jumlahUnik   = $sesiAktif->pluck('pengguna_id')->unique()->count();

        // Kumpulkan mengikut kaedah log masuk
        $mengikutKaedah = $sesiAktif->groupBy('kaedah')->map->count();

        return view('sesi-aktif.index', compact(
            'sesiAktif',
            'sesiSendiri',
            'jumlahSesi',
            'jumlahUnik',
            'mengikutKaedah',
            'hayatMenit',
        ));
    }

    /**
     * Paksa log keluar seseorang pengguna — semua sesi mereka dipadam.
     */
    public function paksaLogKeluar(User $pengguna)
    {
        abort_unless(auth()->user()->isPentadbir(), 403);

        if ($pengguna->id === Auth::id()) {
            return back()->with('error', 'Anda tidak boleh memaksa log keluar akaun sendiri.');
        }

        // Set cache flag — middleware Authenticate akan detect pada request seterusnya
        Cache::put("paksa_log_keluar_{$pengguna->id}", true, now()->addHours(12));

        // Padam semua sesi pengguna dari jadual sesi_aktif
        $kiraan = SesiAktif::where('pengguna_id', $pengguna->id)->delete();

        AuditLogger::catat('paksa_log_keluar', $pengguna, [
            'pengguna_id'   => $pengguna->id,
            'pengguna_nama' => $pengguna->name,
            'sesi_dipadam'  => $kiraan,
        ], Auth::user()->name.' memaksa log keluar '.$pengguna->name);

        return back()->with('success', $pengguna->name.' berjaya dipaksa log keluar dari semua sesi.');
    }
}
