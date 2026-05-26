<?php
/**
 * iBook --- Sistem Pengurusan Bilik Mesyuarat
 * Copyright (c) 2026 Bahagian Pengurusan Teknologi Maklumat (BPTM)
 * Hak cipta terpelihara. Dilarang meniru, menyalin, mengubah suai, atau
 * mengedar perisian ini tanpa kebenaran bertulis daripada pemilik hak cipta.
 *
 * Unauthorized copying, modification, distribution, or use of this software,
 * via any medium, is strictly prohibited. Proprietary and confidential.
 */


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
        $request->validate([
            'tarikh'  => ['required', 'date'],
            'sesi'    => ['nullable', 'in:pagi,petang,semua'],
            'peserta' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], [
            'tarikh.required' => 'Tarikh diperlukan.',
            'tarikh.date'     => 'Format tarikh tidak sah.',
            'sesi.in'         => 'Nilai sesi tidak sah.',
            'peserta.integer' => 'Bilangan peserta tidak sah.',
        ]);

        $tarikh      = $request->tarikh;
        $sesiPilihan = $request->sesi ?? 'semua';
        $peserta     = max(1, (int) $request->peserta);

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

    /**
     * Jadual ketersediaan seminggu (Isnin–Ahad) — untuk mod Jadual Minggu.
     * Mengembalikan semua bilik × semua hari × pagi/petang dalam satu respons.
     */
    public function minggu(Request $request)
    {
        $request->validate([
            'tarikh_mula' => ['nullable', 'date'],
            'peserta'     => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $peserta = max(1, (int) ($request->peserta ?? 1));

        // Mula dari Isnin minggu yang mengandungi tarikh_mula
        $mula = $request->tarikh_mula
            ? \Carbon\Carbon::parse($request->tarikh_mula)->startOfWeek(\Carbon\Carbon::MONDAY)
            : now()->startOfWeek(\Carbon\Carbon::MONDAY);

        $tamat = $mula->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        // Jana 7 hari: Isnin → Ahad
        $hari = [];
        for ($i = 0; $i < 7; $i++) {
            $hari[] = $mula->copy()->addDays($i)->toDateString();
        }

        $bilikList = BilikMesyuarat::where('status', 'aktif')->orderBy('nama')->get();

        // Ambil semua tempahan untuk minggu ini — satu query sahaja
        $cacheKey = 'ketersediaan_minggu_' . $mula->toDateString();
        $ditempahMap = Cache::remember($cacheKey, 60, function () use ($mula, $tamat) {
            $rows = Tempahan::whereBetween('tarikh', [$mula->toDateString(), $tamat->toDateString()])
                ->where('status', '!=', Tempahan::STATUS_DITOLAK)
                ->get(['bilik_id', 'tarikh', 'sesi']);

            // Bina map: [bilik_id][tarikh_string] => [sesi, ...]
            $map = [];
            foreach ($rows as $row) {
                $bilikId = $row->bilik_id;
                $t = $row->tarikh instanceof \Carbon\Carbon
                    ? $row->tarikh->toDateString()
                    : substr((string) $row->tarikh, 0, 10);
                $map[$bilikId][$t][] = $row->sesi;
            }
            return $map;
        });

        $hasil = $bilikList->map(function ($bilik) use ($hari, $peserta, $ditempahMap) {
            $kapasitiBoleh = $bilik->kapasiti >= $peserta;
            $slots = [];
            foreach ($hari as $tarikh) {
                $ditempah = $ditempahMap[$bilik->id][$tarikh] ?? [];
                $slots[$tarikh] = [
                    'pagi'   => !in_array('pagi',   $ditempah),
                    'petang' => !in_array('petang', $ditempah),
                ];
            }
            return [
                'id'             => $bilik->id,
                'nama'           => $bilik->nama,
                'kapasiti'       => $bilik->kapasiti,
                'kapasiti_cukup' => $kapasitiBoleh,
                'slot'           => $slots,
            ];
        })->values();

        return response()->json([
            'tarikh_mula'  => $mula->toDateString(),
            'tarikh_tamat' => $tamat->toDateString(),
            'hari'         => $hari,
            'peserta'      => $peserta,
            'bilik'        => $hasil,
        ]);
    }
}
