<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KetersediaanController extends Controller
{
    public function index()
    {
        $bilikAktif = BilikMesyuarat::where('status', 'aktif')->orderBy('nama')->get();
        return view('ketersediaan.index', compact('bilikAktif'));
    }

    public function cek(Request $request)
    {
        $tarikh      = $request->tarikh;
        $sesiPilihan = $request->sesi ?? 'semua';
        $peserta     = max(1, (int) $request->peserta);

        if (!$tarikh) {
            return response()->json(['error' => 'Tarikh diperlukan'], 422);
        }

        $sesiList = match($sesiPilihan) {
            'pagi'   => ['pagi'],
            'petang' => ['petang'],
            default  => ['pagi', 'petang'],
        };

        $bilikList = BilikMesyuarat::where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        // Satu query sahaja untuk semua bilik + sesi pada tarikh tersebut.
        // Cache 60 saat — mengelak query berulang untuk tarikh/sesi yang sama.
        // Cache dikosongkan apabila tempahan baharu dicipta (bumpKalendarCacheVersion).
        $cacheKey = 'ketersediaan_' . $tarikh . '_' . $sesiPilihan;

        $ditempahMap = Cache::remember($cacheKey, 60, function () use ($tarikh, $sesiList) {
            return Tempahan::where('tarikh', $tarikh)
                ->whereIn('sesi', $sesiList)
                ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                ->get(['bilik_id', 'sesi'])
                ->groupBy('bilik_id')
                ->map(fn($rows) => $rows->pluck('sesi')->all());
        });

        $hasil = $bilikList->map(function ($bilik) use ($sesiList, $peserta, $ditempahMap) {
            $sesiDitempah = $ditempahMap->get($bilik->id, []);

            $statusSesi = [];
            foreach ($sesiList as $sesi) {
                $statusSesi[$sesi] = !in_array($sesi, $sesiDitempah);
            }

            $semuaTersedia   = !in_array(false, $statusSesi, true);
            $adaYangTersedia = in_array(true, $statusSesi, true);
            $kapasitiBoleh   = $bilik->kapasiti >= $peserta;

            return [
                'id'             => $bilik->id,
                'nama'           => $bilik->nama,
                'kapasiti'       => $bilik->kapasiti,
                'lokasi'         => $bilik->lokasi,
                'kemudahan'      => $bilik->kemudahan ?? [],
                'gambar'         => $bilik->gambar,
                'status_sesi'    => $statusSesi,
                'semua_tersedia' => $semuaTersedia,
                'ada_tersedia'   => $adaYangTersedia,
                'kapasiti_cukup' => $kapasitiBoleh,
                'boleh_tempah'   => $semuaTersedia && $kapasitiBoleh,
            ];
        });

        // Susun: sepenuhnya tersedia & cukup kapasiti → sebahagian → tidak boleh
        $sorted = $hasil->sortByDesc(function ($b) {
            if ($b['boleh_tempah']) return 3;
            if ($b['ada_tersedia'] && $b['kapasiti_cukup']) return 2;
            if ($b['ada_tersedia']) return 1;
            return 0;
        })->values();

        return response()->json([
            'tarikh'  => $tarikh,
            'sesi'    => $sesiPilihan,
            'peserta' => $peserta,
            'bilik'   => $sorted,
        ]);
    }
}
