<?php

namespace App\Http\Controllers;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use Illuminate\Http\Request;

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

        $hasil = $bilikList->map(function ($bilik) use ($tarikh, $sesiList, $peserta) {
            $statusSesi = [];
            foreach ($sesiList as $sesi) {
                $ditempah = Tempahan::where('bilik_id', $bilik->id)
                    ->where('tarikh', $tarikh)
                    ->where('sesi', $sesi)
                    ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                    ->exists();
                $statusSesi[$sesi] = !$ditempah; // true = masih kosong
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
