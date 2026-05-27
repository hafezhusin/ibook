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

use App\Exports\LaporanExport;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /** Cache TTL: 24 jam untuk tahun lalu (data frozen), 15 minit untuk tahun semasa */
    const CACHE_LALU_TTL = 86400;
    const CACHE_INI_TTL  = 900;

    public function index(Request $request)
    {
        $tahun       = (int) $request->get('tahun', now()->year);
        $bilikFilter = $request->filled('bilik_id') ? (int) $request->bilik_id : null;
        $user        = Auth::user();
        $isStaf      = $user->isStaf();
        $isPentadbir = $user->isPentadbir();
        $senaraiTahun = range(now()->year, now()->year - 4);
        $senaraibilik = BilikMesyuarat::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama']);

        // ── Item 5: Log audit akses laporan ──
        AuditLogger::catat('akses_laporan', null, [
            'tahun'   => $tahun,
            'peranan' => $user->peranan,
        ]);

        // =============================================
        // PAPARAN STAF — statistik unit sendiri sahaja
        // =============================================
        if ($isStaf) {
            $jabatan    = $user->jabatan;
            $userIdUnit = DB::table('users')->where('jabatan', $jabatan)->pluck('id')->toArray();

            $dataBulan        = $this->kiraBulan($tahun, $userIdUnit);
            $dataBulanSesi    = $this->kiraBulanSesi($tahun, $userIdUnit);
            $mengikutKategori = Tempahan::selectRaw('kategori, COUNT(*) as jumlah')
                ->whereYear('tarikh', $tahun)
                ->whereIn('user_id', $userIdUnit)
                ->groupBy('kategori')->get();

            $totalDiluluskan = Tempahan::whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_DILULUSKAN)
                ->whereIn('user_id', $userIdUnit)->count();

            $totalDitolak = Tempahan::whereYear('tarikh', $tahun)
                ->where('status', Tempahan::STATUS_DITOLAK)
                ->whereIn('user_id', $userIdUnit)->count();

            return view('laporan.index', compact(
                'dataBulan', 'dataBulanSesi', 'mengikutKategori', 'tahun', 'senaraiTahun',
                'jabatan', 'totalDiluluskan', 'totalDitolak', 'isStaf', 'senaraibilik', 'bilikFilter'
            ));
        }

        // =============================================
        // PAPARAN ADMIN / URUS SETIA — dengan cache (Item 6)
        // =============================================
        $isTahunSemasa = ($tahun === now()->year);
        $ttl      = $isTahunSemasa ? self::CACHE_INI_TTL : self::CACHE_LALU_TTL;
        $cacheKey = "laporan_v2_admin_{$tahun}" . ($bilikFilter ? "_b{$bilikFilter}" : '');

        // Data dikira sekali dan dicache (tidak bergantung kepada pengguna)
        $data = Cache::remember($cacheKey, $ttl, fn () => $this->kiraLaporanAdmin($tahun, $bilikFilter));

        // Tambah nilai yang bergantung kepada pengguna (tidak boleh dicache)
        $data['isPentadbir']  = $isPentadbir;
        $data['isStaf']       = false;
        $data['tahun']        = $tahun;
        $data['senaraiTahun'] = $senaraiTahun;
        $data['senaraibilik'] = $senaraibilik;
        $data['bilikFilter']  = $bilikFilter;

        return view('laporan.index', $data);
    }

    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $tahun       = (int) $request->get('tahun', now()->year);
        $bilikFilter = $request->filled('bilik_id') ? (int) $request->bilik_id : null;

        $data          = $this->kiraLaporanAdmin($tahun, $bilikFilter);
        $data['tahun'] = $tahun;

        AuditLogger::catat('eksport_laporan_pdf', null, ['tahun' => $tahun]);

        $pdf = Pdf::loadView('laporan.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
                'chroot'               => public_path(),
                'dpi'                  => 96,
            ]);

        return $pdf->download("Laporan_Statistik_iBook_{$tahun}.pdf");
    }

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $tahun       = (int) $request->get('tahun', now()->year);
        $bilikFilter = $request->filled('bilik_id') ? (int) $request->bilik_id : null;

        $data = $this->kiraLaporanAdmin($tahun, $bilikFilter);

        AuditLogger::catat('eksport_laporan_excel', null, ['tahun' => $tahun]);

        return Excel::download(
            new LaporanExport($data, $tahun),
            "Laporan_Statistik_iBook_{$tahun}.xlsx"
        );
    }

    // ─────────────────────────────────────────────────────────
    // Kira semua data laporan admin — boleh dicache selamat
    // ─────────────────────────────────────────────────────────
    private function kiraLaporanAdmin(int $tahun, ?int $bilikId = null): array
    {
        // ── 1. Tempahan mengikut bulan ──
        $dataBulan       = $this->kiraBulan($tahun, [], $bilikId);
        $dataBulanSesi   = $this->kiraBulanSesi($tahun, [], $bilikId);
        $totalDiluluskan = array_sum($dataBulan);

        // ── 2. Tempahan mengikut kategori ──
        $mengikutKategori = Tempahan::selectRaw('kategori, COUNT(*) as jumlah')
            ->whereYear('tarikh', $tahun)
            ->when($bilikId, fn ($q) => $q->where('bilik_id', $bilikId))
            ->groupBy('kategori')
            ->get();

        // ── 3. Tempahan mengikut unit rasmi ──
        $mengikutUnit = DB::table('tempahan')
            ->join('users', 'tempahan.user_id', '=', 'users.id')
            ->whereYear('tempahan.tarikh', $tahun)
            ->where('tempahan.status', Tempahan::STATUS_DILULUSKAN)
            ->whereIn('users.jabatan', $this->unitRasmi())
            ->select('users.jabatan as unit', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('users.jabatan')
            ->orderByDesc('jumlah')
            ->get();

        // ── 4. Top 10 pemohon ──
        $top10Pengguna = DB::table('tempahan')
            ->join('users', 'tempahan.user_id', '=', 'users.id')
            ->whereYear('tempahan.tarikh', $tahun)
            ->select(
                'users.id',
                'users.name',
                'users.jabatan',
                DB::raw('COUNT(*) as jumlah'),
                // Guna petikan tunggal — MySQL dan SQLite sama-sama terima
                DB::raw("SUM(CASE WHEN tempahan.status = 'diluluskan' THEN 1 ELSE 0 END) AS jumlah_diluluskan"),
                DB::raw("SUM(CASE WHEN tempahan.status = 'ditolak' THEN 1 ELSE 0 END) AS jumlah_ditolak")
            )
            ->groupBy('users.id', 'users.name', 'users.jabatan')
            ->orderByDesc('jumlah')
            ->limit(10)
            ->get();

        // ── 5. Ringkasan bilik — FIX N+1 + peratusan betul (Item 4) ──
        // Sebelum ini: $b->penggunaan_bulan_ini memanggil accessor → N+1 query,
        // dan nilai salah kerana berasaskan bulan semasa, bukan tahun yang dipilih.
        // Sekarang: dikira terus dalam map() menggunakan eager-loaded collection.
        $isKabisat     = Carbon::createFromDate($tahun, 1, 1)->isLeapYear();
        $maxSesiTahun  = ($isKabisat ? 366 : 365) * 2;

        $bilik = BilikMesyuarat::with(['tempahan' => function ($q) use ($tahun) {
            $q->whereYear('tarikh', $tahun)
              ->where('status', Tempahan::STATUS_DILULUSKAN);
        }])->get()->map(function ($b) use ($maxSesiTahun) {
            $jumlah    = $b->tempahan->count();
            // $maxSesiTahun adalah 730 atau 732 — sentiasa > 0, bahagi terus.
            $peratusan = (int) round(($jumlah / $maxSesiTahun) * 100);
            return [
                'nama'            => $b->nama,
                'kapasiti'        => $b->kapasiti,
                'jumlah_tempahan' => $jumlah,
                'peratusan'       => $peratusan,
            ];
        })->sortByDesc('jumlah_tempahan')->values();

        // ── 6. KPI eksekutif + Insight sentences (Items 2 & 7) ──
        $unitPalingAktif  = $mengikutUnit->first();
        $bilikPalingGuna  = $bilik->first();
        $purataPenggunaan = $bilik->isNotEmpty()
            ? (int) round($bilik->avg('peratusan'))
            : 0;

        $insightUnit  = $unitPalingAktif
            ? "{$unitPalingAktif->unit} mencatatkan {$unitPalingAktif->jumlah} tempahan — unit paling aktif tahun {$tahun}."
            : null;
        $insightBilik = $bilikPalingGuna && $bilikPalingGuna['jumlah_tempahan'] > 0
            ? "{$bilikPalingGuna['nama']} adalah bilik paling banyak digunakan dengan {$bilikPalingGuna['jumlah_tempahan']} sesi ({$bilikPalingGuna['peratusan']}% kadar penggunaan tahun ini)."
            : null;

        return compact(
            'dataBulan', 'dataBulanSesi', 'totalDiluluskan',
            'mengikutKategori', 'mengikutUnit',
            'top10Pengguna', 'bilik',
            'unitPalingAktif', 'bilikPalingGuna', 'purataPenggunaan',
            'insightUnit', 'insightBilik'
        );
    }

    // ─────────────────────────────────────────────────────────
    // Kira data bulan — boleh tapis mengikut user IDs (unit staf)
    // ─────────────────────────────────────────────────────────
    private function kiraBulan(int $tahun, array $userIds = [], ?int $bilikId = null): array
    {
        $isSqlite   = DB::connection()->getDriverName() === 'sqlite';
        $selectExpr = $isSqlite
            ? "CAST(strftime('%m', tarikh) AS INTEGER) AS bulan, COUNT(*) AS jumlah"
            : 'MONTH(tarikh) AS bulan, COUNT(*) AS jumlah';
        $groupExpr  = $isSqlite ? "strftime('%m', tarikh)" : 'MONTH(tarikh)';

        $query = Tempahan::selectRaw($selectExpr)
            ->whereYear('tarikh', $tahun)
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->when(!empty($userIds), fn ($q) => $q->whereIn('user_id', $userIds))
            ->when($bilikId, fn ($q) => $q->where('bilik_id', $bilikId))
            ->groupByRaw($groupExpr)
            ->orderByRaw($groupExpr);

        $mengikutBulan = $query->get()->keyBy('bulan');
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            // @phpstan-ignore-next-line nullsafe.neverNull, property.notFound — jumlah dari selectRaw
            $data[] = $mengikutBulan->get($i)?->jumlah ?? 0;
        }
        return $data;
    }

    /**
     * Kira tempahan per bulan diasingkan mengikut sesi (pagi/petang).
     * Digunakan untuk stacked bar chart sesi.
     */
    private function kiraBulanSesi(int $tahun, array $userIds = [], ?int $bilikId = null): array
    {
        $isSqlite   = DB::connection()->getDriverName() === 'sqlite';
        $selectExpr = $isSqlite
            ? "CAST(strftime('%m', tarikh) AS INTEGER) AS bulan, sesi, COUNT(*) AS jumlah"
            : 'MONTH(tarikh) AS bulan, sesi, COUNT(*) AS jumlah';
        $groupExpr  = $isSqlite ? "strftime('%m', tarikh), sesi" : 'MONTH(tarikh), sesi';

        $rows = Tempahan::selectRaw($selectExpr)
            ->whereYear('tarikh', $tahun)
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->when(!empty($userIds), fn ($q) => $q->whereIn('user_id', $userIds))
            ->when($bilikId, fn ($q) => $q->where('bilik_id', $bilikId))
            ->groupByRaw($groupExpr)
            ->orderByRaw($groupExpr)
            ->get();

        $pagi   = array_fill(0, 12, 0);
        $petang = array_fill(0, 12, 0);

        foreach ($rows as $r) {
            // @phpstan-ignore-next-line property.notFound — bulan/jumlah dari selectRaw dinamik
            $idx = $r->bulan - 1;
            // @phpstan-ignore-next-line property.notFound
            if ($r->sesi === 'pagi')   $pagi[$idx]   = $r->jumlah;
            // @phpstan-ignore-next-line property.notFound
            if ($r->sesi === 'petang') $petang[$idx] = $r->jumlah;
        }

        return ['pagi' => $pagi, 'petang' => $petang];
    }

    // ─────────────────────────────────────────────────────────
    // Senarai unit rasmi BPTM ANM
    // ─────────────────────────────────────────────────────────
    private function unitRasmi(): array
    {
        return [
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
    }
}
