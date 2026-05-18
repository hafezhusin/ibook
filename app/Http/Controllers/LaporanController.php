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

        // Senarai unit rasmi dalam direktori BPTM ANM
        $unitRasmi = [
            'Pejabat Pengarah',
            'Seksyen Pengurusan Pelanggan',
            'Unit Pentadbiran dan Pengurusan Kewangan',
            'Unit Authorization',
            'Unit Pengurusan Antaramuka / Integrasi',
            'Unit Khidmat Pelanggan',
            'Seksyen Digital dan Projek Khas',
            'Seksyen Pengurusan Aplikasi FICO',
            'Unit Lejar Am dan Kawalan Data Induk',
            'Unit Pengurusan Dana, Pinjaman dan Pelaburan',
            'Sub Unit LMS',
            'Sub Unit CM',
            'Sub Unit TR',
            'Unit Pelaporan Strategik (BWBI)',
            'Unit Bayaran',
            'Unit Logistik',
            'Unit Terimaan',
            'Unit Aset',
            'Seksyen Pengurusan Aplikasi Non FICO',
            'Unit Pengurusan Wang Tak Dituntut dan Pengkosan',
            'Unit Pengurusan Wang Tak Dituntut',
            'Unit Pengkosan',
            'Unit Gaji',
            'Unit Maklumat Online (eApps)',
            'Seksyen Perkhidmatan ICT 1',
            'Unit Pengurusan Infrastruktur',
            'Unit Operasi Aplikasi Teras',
            'Seksyen Perkhidmatan ICT 2',
            'Unit Pengurusan Rangkaian dan Keselamatan ICT',
            'Unit Aplikasi Gunasama',
            'Seksyen Kualiti dan Perancangan',
        ];

        // Statistik mengikut unit (hanya unit rasmi direktori BPTM)
        $mengikutUnit = DB::table('tempahan')
            ->join('users', 'tempahan.user_id', '=', 'users.id')
            ->whereYear('tempahan.tarikh', $tahun)
            ->where('tempahan.status', Tempahan::STATUS_DILULUSKAN)
            ->whereIn('users.jabatan', $unitRasmi)
            ->select('users.jabatan as unit', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('users.jabatan')
            ->orderByDesc('jumlah')
            ->get();

        // Top 10 pemohon terbanyak
        $top10Pengguna = DB::table('tempahan')
            ->join('users', 'tempahan.user_id', '=', 'users.id')
            ->whereYear('tempahan.tarikh', $tahun)
            ->select(
                'users.id',
                'users.name',
                'users.jabatan',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('SUM(CASE WHEN tempahan.status = "diluluskan" THEN 1 ELSE 0 END) as jumlah_diluluskan'),
                DB::raw('SUM(CASE WHEN tempahan.status = "ditolak" THEN 1 ELSE 0 END) as jumlah_ditolak')
            )
            ->groupBy('users.id', 'users.name', 'users.jabatan')
            ->orderByDesc('jumlah')
            ->limit(10)
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
            'mengikutUnit',
            'top10Pengguna',
            'tahun',
            'senaraiTahun',
            'isStaf'
        ));
    }
}
