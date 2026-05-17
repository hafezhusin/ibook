<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $bulanLepas = now()->subMonth()->month;
        $tahunLepas = now()->subMonth()->year;

        $query = Tempahan::query();
        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        // Tempahan bulan ini
        $jumlahTempahan = (clone $query)
            ->whereMonth('tarikh', $bulanIni)
            ->whereYear('tarikh', $tahunIni)
            ->count();

        // Tempahan bulan lepas (untuk trend)
        $jumlahTempahanLepas = (clone $query)
            ->whereMonth('tarikh', $bulanLepas)
            ->whereYear('tarikh', $tahunLepas)
            ->count();

        // Kira trend (%)
        $trend = 0;
        $trendNaik = true;
        if ($jumlahTempahanLepas > 0) {
            $trend = (int) round((($jumlahTempahan - $jumlahTempahanLepas) / $jumlahTempahanLepas) * 100);
            $trendNaik = $trend >= 0;
        } elseif ($jumlahTempahan > 0) {
            $trend = 100;
            $trendNaik = true;
        }

        // Mesyuarat hari ini
        $mesyuaratHariIni = (clone $query)
            ->whereDate('tarikh', today())
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->count();

        // Bilik aktif
        $bilik = BilikMesyuarat::where('status', 'aktif')->get();

        // Bilik yang sudah ditempah hari ini (semak sesi pagi DAN petang)
        $bilikDitempahPagi = Tempahan::whereDate('tarikh', today())
            ->where('sesi', 'pagi')
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->pluck('bilik_id');

        $bilikDitempahPetang = Tempahan::whereDate('tarikh', today())
            ->where('sesi', 'petang')
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->pluck('bilik_id');

        // Bilik "sepenuhnya penuh" = ditempah KEDUA-DUA sesi
        $bilikPenuh = $bilikDitempahPagi->intersect($bilikDitempahPetang);

        $jumlahBilikAktif    = $bilik->count();
        $jumlahBilikTersedia = max(0, $jumlahBilikAktif - $bilikPenuh->count());

        // Bilik dengan sekurang-kurangnya satu sesi tersedia hari ini
        $bilikAdaSesiKosong = $bilik->filter(function ($b) use ($bilikPenuh) {
            return !$bilikPenuh->contains($b->id);
        })->count();

        $kadarPenggunaan = 0;
        if ($bilik->count() > 0) {
            $total = $bilik->sum(fn($b) => $b->penggunaan_bulan_ini);
            $kadarPenggunaan = (int) round($total / $bilik->count());
        }

        $mesyuaratAkanDatang = (clone $query)
            ->with(['bilik', 'pengguna'])
            ->where('tarikh', '>=', today())
            ->where('tarikh', '<=', today()->addDays(7))
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->orderBy('tarikh')
            ->orderBy('masa_mula')
            ->limit(10)
            ->get();

        $penggunaanBilik = $bilik->map(fn($b) => [
            'nama'      => $b->nama,
            'peratusan' => $b->penggunaan_bulan_ini,
        ]);

        return view('dashboard.index', compact(
            'jumlahTempahan',
            'jumlahTempahanLepas',
            'trend',
            'trendNaik',
            'mesyuaratHariIni',
            'jumlahBilikTersedia',
            'jumlahBilikAktif',
            'bilikAdaSesiKosong',
            'kadarPenggunaan',
            'mesyuaratAkanDatang',
            'penggunaanBilik',
            'bulanIni',
            'tahunIni',
            'bulanLepas',
            'tahunLepas'
        ));
    }
}
