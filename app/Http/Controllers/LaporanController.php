<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $user  = Auth::user();
        $isStaf = $user->isStaf();

        $senaraiTahun = range(now()->year, now()->year - 4);

        // =============================================
        // PAPARAN STAF — statistik unit sendiri sahaja
        // =============================================
        if ($isStaf) {
            $jabatan = $user->jabatan;

            // ID semua pengguna dalam jabatan yang sama
            $userIdUnit = DB::table('users')
                ->where('jabatan', $jabatan)
                ->pluck('id');

            // Tempahan unit mengikut bulan
            $mengikutBulan = Tempahan::selectRaw('MONTH(tarikh) as bulan, COUNT(*) as jumlah')
                ->whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_DILULUSKAN)
                ->whereIn('user_id', $userIdUnit)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->keyBy('bulan');

            $dataBulan = [];
            for ($i = 1; $i <= 12; $i++) {
                $dataBulan[] = $mengikutBulan->get($i)?->jumlah ?? 0;
            }

            // Tempahan unit mengikut kategori
            $mengikutKategori = Tempahan::selectRaw('kategori, COUNT(*) as jumlah')
                ->whereYear('tarikh', $tahun)
                ->whereIn('user_id', $userIdUnit)
                ->groupBy('kategori')
                ->get();

            // Kad ringkasan unit
            $totalDiluluskan = Tempahan::whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_DILULUSKAN)
                ->whereIn('user_id', $userIdUnit)
                ->count();

            $totalMenunggu = Tempahan::whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_MENUNGGU)
                ->whereIn('user_id', $userIdUnit)
                ->count();

            $totalDitolak = Tempahan::whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_DITOLAK)
                ->whereIn('user_id', $userIdUnit)
                ->count();

            return view('laporan.index', compact(
                'dataBulan',
                'mengikutKategori',
                'tahun',
                'senaraiTahun',
                'jabatan',
                'totalDiluluskan',
                'totalMenunggu',
                'totalDitolak',
                'isStaf'
            ));
        }

        // =============================================
        // PAPARAN PENTADBIR / URUS SETIA — semua data
        // =============================================

        // Tempahan mengikut bulan
        $mengikutBulan = Tempahan::selectRaw('MONTH(tarikh) as bulan, COUNT(*) as jumlah')
            ->whereYear('tarikh', $tahun)
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $dataBulan = [];
        for ($i = 1; $i <= 12; $i++) {
            $dataBulan[] = $mengikutBulan->get($i)?->jumlah ?? 0;
        }

        // Tempahan mengikut kategori
        $mengikutKategori = Tempahan::selectRaw('kategori, COUNT(*) as jumlah')
            ->whereYear('tarikh', $tahun)
            ->groupBy('kategori')
            ->get();

        // Ringkasan penggunaan bilik
        $bilik = BilikMesyuarat::with(['tempahan' => function ($q) use ($tahun) {
            $q->whereYear('tarikh', $tahun)->where('status', Tempahan::STATUS_DILULUSKAN);
        }])->get()->map(function ($b) {
            $jumlahTempahan = $b->tempahan->count();
            $jamDigunakan = $b->tempahan->reduce(function ($carry, $t) {
                $mula  = strtotime($t->masa_mula);
                $tamat = strtotime($t->masa_tamat);
                return $carry + (($tamat - $mula) / 3600);
            }, 0);

            return [
                'nama'            => $b->nama,
                'kapasiti'        => $b->kapasiti,
                'jumlah_tempahan' => $jumlahTempahan,
                'jam_digunakan'   => round($jamDigunakan),
                'peratusan'       => $b->penggunaan_bulan_ini,
            ];
        });

        $isStaf = false;

        return view('laporan.index', compact(
            'dataBulan',
            'mengikutKategori',
            'bilik',
            'tahun',
            'senaraiTahun',
            'isStaf'
        ));
    }
}
