<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $query = Tempahan::query();
        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

        $jumlahTempahan = (clone $query)
            ->whereMonth('tarikh', $bulanIni)
            ->whereYear('tarikh', $tahunIni)
            ->count();

        $menungguKelulusan = Tempahan::where('status', Tempahan::STATUS_MENUNGGU)->count();

        $mesyuaratHariIni = (clone $query)
            ->whereDate('tarikh', today())
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->count();

        $bilik = BilikMesyuarat::where('status', 'aktif')->get();
        $kadarPenggunaan = 0;
        if ($bilik->count() > 0) {
            $total = $bilik->sum(fn($b) => $b->penggunaan_bulan_ini);
            $kadarPenggunaan = (int) round($total / $bilik->count());
        }

        $mesyuaratAkanDatang = (clone $query)
            ->with(['bilik', 'pengguna'])
            ->where('tarikh', '>=', today())
            ->where('tarikh', '<=', today()->addDays(7))
            ->where('status', '!=', Tempahan::STATUS_DITOLAK)
            ->orderBy('tarikh')
            ->orderBy('masa_mula')
            ->limit(10)
            ->get();

        $menungguList = Tempahan::with(['bilik', 'pengguna'])
            ->where('status', Tempahan::STATUS_MENUNGGU)
            ->orderBy('tarikh')
            ->limit(5)
            ->get();

        $penggunaanBilik = $bilik->map(fn($b) => [
            'nama' => $b->nama,
            'peratusan' => $b->penggunaan_bulan_ini,
        ]);

        return view('dashboard.index', compact(
            'jumlahTempahan',
            'menungguKelulusan',
            'mesyuaratHariIni',
            'kadarPenggunaan',
            'mesyuaratAkanDatang',
            'menungguList',
            'penggunaanBilik',
            'bulanIni',
            'tahunIni'
        ));
    }
}
