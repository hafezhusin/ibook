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

namespace App\Services;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Kunci cache unik bagi setiap pengguna, peranan & filter bahagian.
     * Staf mendapat data unit sendiri, admin/urus setia mengikut skop bahagian.
     */
    private function kunciCache(User $user, ?int $bahagianFilter = null): string
    {
        // Sertakan versi supaya luputkanSemuaCache() benar-benar buang semua entri cache.
        $versi = Cache::get('dashboard.cache.version', 1);
        if ($user->isStaf()) {
            $skop = "staf.{$user->id}";
        } else {
            // Urus setia berlainan bahagian perlu cache berasingan
            $bahagianDim = $user->bahagian_id ?? 'all';
            // Pentadbir sistem boleh filter by bahagian — sertakan dalam kunci
            $filterDim   = $bahagianFilter ? ".f{$bahagianFilter}" : '';
            $skop = "admin.{$user->peranan}.h{$bahagianDim}{$filterDim}";
        }

        return "dashboard.v{$versi}.{$skop}";
    }

    /**
     * Dapatkan semua data dashboard.
     * Hasil di-cache mengikut TTL dalam config/ibook.php.
     */
    public function getData(User $user, ?int $bahagianFilter = null): array
    {
        $ttl = config('ibook.cache.dashboard', 300);

        return Cache::remember($this->kunciCache($user, $bahagianFilter), $ttl, function () use ($user, $bahagianFilter) {
            return $this->kiraData($user, $bahagianFilter);
        });
    }

    /**
     * Padam cache dashboard untuk pengguna tertentu.
     * Dipanggil bila ada tempahan baru / dikemaskini.
     * Guna luputkanSemuaCache() jika filter bahagian berbeza-beza.
     */
    public function luputkanCache(User $user): void
    {
        // Buang cache tanpa filter (paparan biasa)
        Cache::forget($this->kunciCache($user, null));
        // Bump versi keseluruhan supaya semua filter variant turut luput
        $this->luputkanSemuaCache();
    }

    /**
     * Padam semua cache dashboard (untuk admin selepas kemaskini besar).
     */
    public function luputkanSemuaCache(): void
    {
        // File cache tidak sokong tags — guna versi bump
        Cache::increment('dashboard.cache.version');
    }

    /**
     * Kira semua statistik dashboard dari DB.
     */
    private function kiraData(User $user, ?int $bahagianFilter = null): array
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $bulanLepas = now()->subMonth()->month;
        $tahunLepas = now()->subMonth()->year;

        // ── Bilik skop — apply bahagian filter jika ada ──────────────────
        $bilik = BilikMesyuarat::where('status', 'aktif')
            ->untukPengguna($user)
            ->when($bahagianFilter, fn ($q) => $q->where('bahagian_id', $bahagianFilter))
            ->get();

        $bilikIds = $bilik->pluck('id');

        // ── Query asas tempahan mengikut skop ────────────────────────────
        // Staf: tempahan sendiri sahaja
        // Pentadbir + filter bahagian: tempahan dalam bilik bahagian berkenaan
        // Pentadbir tanpa filter: semua tempahan
        $query = Tempahan::query();
        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        } elseif ($bahagianFilter) {
            $query->whereIn('bilik_id', $bilikIds);
        }
        // Kecualikan tempahan dibatalkan (auto-batal sistem) dari semua statistik.
        // Termasuk: jumlah bulan ini, trend bulanan, kategori.
        // Tidak perlu kira: mesyuaratHariIni, penggunaanMap, akan_datang — dah ada filter DILULUSKAN.
        $query->where('status', '!=', Tempahan::STATUS_DIBATALKAN);

        // Tempahan bulan ini & bulan lepas (untuk trend)
        $jumlahTempahan = (clone $query)
            ->whereMonth('tarikh', $bulanIni)
            ->whereYear('tarikh', $tahunIni)
            ->count();

        $jumlahTempahanLepas = (clone $query)
            ->whereMonth('tarikh', $bulanLepas)
            ->whereYear('tarikh', $tahunLepas)
            ->count();

        // Kira peratusan trend
        [$trend, $trendNaik] = $this->kiraTrend($jumlahTempahan, $jumlahTempahanLepas);

        // Mesyuarat hari ini
        $mesyuaratHariIni = (clone $query)
            ->whereDate('tarikh', today())
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->count();
        $esok = today()->addDay();

        // Satu query untuk semua slot hari ini + esok — ganti 4 query berasingan
        $slotDitempah = Tempahan::whereIn('tarikh', [today(), $esok])
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->select('tarikh', 'sesi', 'bilik_id')
            ->get()
            ->groupBy(fn ($r) => $r->tarikh->toDateString().'|'.$r->sesi);

        $ambilSlot = fn ($hari, $sesi) => $slotDitempah
            ->get($hari->toDateString().'|'.$sesi, collect())
            ->pluck('bilik_id');

        $bilikDitempahPagi = $ambilSlot(today(), 'pagi');
        $bilikDitempahPetang = $ambilSlot(today(), 'petang');
        $bilikDitempahPagiEsok = $ambilSlot($esok, 'pagi');
        $bilikDitempahPetangEsok = $ambilSlot($esok, 'petang');
        $bilikPenuh = $bilikDitempahPagi->intersect($bilikDitempahPetang);

        $jumlahBilikAktif = $bilik->count();
        $jumlahBilikTersedia = max(0, $jumlahBilikAktif - $bilikPenuh->count());

        // Pra-kira penggunaan bilik bulan ini dalam SATU query (elak N+1 daripada accessor)
        $maxSesiSebulan = now()->daysInMonth * 2; // 2 sesi × bilangan hari
        $penggunaanMap = Tempahan::selectRaw('bilik_id, COUNT(*) as jumlah')
            ->whereMonth('tarikh', $bulanIni)
            ->whereYear('tarikh', $tahunIni)
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->whereIn('bilik_id', $bilikIds)
            ->groupBy('bilik_id')
            ->pluck('jumlah', 'bilik_id'); // Collection: bilik_id => jumlah

        // Kadar penggunaan purata bulan ini (%)
        $kadarPenggunaan = 0;
        if ($bilik->count() > 0 && $maxSesiSebulan > 0) {
            $totalPossible = $maxSesiSebulan * $bilik->count();
            $kadarPenggunaan = (int) round($penggunaanMap->sum() / $totalPossible * 100);
        }

        // Mesyuarat akan datang (7 hari)
        $had = config('ibook.had.mesyuarat_akan_datang_dashboard', 10);
        $mesyuaratAkanDatang = (clone $query)
            ->with(['bilik:id,nama', 'pengguna:id,name'])
            ->where('tarikh', '>=', today())
            ->where('tarikh', '<=', today()->addDays(7))
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->orderBy('tarikh')
            ->orderBy('masa_mula')
            ->limit($had)
            ->get();

        // Penggunaan bilik untuk bar chart (guna $penggunaanMap — tanpa query tambahan)
        $penggunaanBilik = $bilik->map(fn ($b) => [
            'nama' => $b->nama,
            'peratusan' => $maxSesiSebulan > 0
                ? (int) round(($penggunaanMap->get($b->id, 0) / $maxSesiSebulan) * 100)
                : 0,
        ]);

        // Ketersediaan bilik hari ini — pagi & petang (guna data yang dah dikira, tiada query baru)
        $ketersediaanHariIni = $bilik->map(fn ($b) => [
            'nama' => $b->nama,
            'kapasiti' => $b->kapasiti,
            'pagi' => ! $bilikDitempahPagi->contains($b->id),
            'petang' => ! $bilikDitempahPetang->contains($b->id),
        ]);

        // Mesyuarat seterusnya milik pengguna ini (sentiasa user-scoped, tanpa mengira peranan)
        $mesyuaratSeterusnya = Tempahan::where('user_id', $user->id)
            ->where('tarikh', '>=', today())
            ->where('status', Tempahan::STATUS_DILULUSKAN)
            ->with('bilik:id,nama')
            ->orderBy('tarikh')
            ->orderBy('masa_mula')
            ->first();

        // Ketersediaan esok — data sudah ada dari query konsolidasi di atas
        $bilikKosongEsokPagi = $bilik->filter(fn ($b) => ! $bilikDitempahPagiEsok->contains($b->id))->count();
        $bilikKosongEsokPetang = $bilik->filter(fn ($b) => ! $bilikDitempahPetangEsok->contains($b->id))->count();

        // ── Trend 6 bulan ─────────────────────────────────────────────
        $trendBulanan = $this->kiraTrendBulanan($query, 6);

        // ── Statistik kategori bulan ini ───────────────────────────────
        $statistikKategori = $this->kiraKategori($query, $bulanIni, $tahunIni);

        return [
            'jumlahTempahan' => $jumlahTempahan,
            'jumlahTempahanLepas' => $jumlahTempahanLepas,
            'trend' => $trend,
            'trendNaik' => $trendNaik,
            'mesyuaratHariIni' => $mesyuaratHariIni,
            'jumlahBilikTersedia' => $jumlahBilikTersedia,
            'jumlahBilikAktif' => $jumlahBilikAktif,
            'bilikAdaSesiKosong' => $bilik->filter(fn ($b) => ! $bilikPenuh->contains($b->id))->count(),
            'kadarPenggunaan' => $kadarPenggunaan,
            'mesyuaratAkanDatang' => $mesyuaratAkanDatang,
            'penggunaanBilik' => $penggunaanBilik,
            'ketersediaanHariIni' => $ketersediaanHariIni,
            'mesyuaratSeterusnya' => $mesyuaratSeterusnya,
            'bilikKosongEsokPagi' => $bilikKosongEsokPagi,
            'bilikKosongEsokPetang' => $bilikKosongEsokPetang,
            'bulanIni' => $bulanIni,
            'tahunIni' => $tahunIni,
            'bulanLepas' => $bulanLepas,
            'tahunLepas' => $tahunLepas,
            'trendBulanan' => $trendBulanan,
            'statistikKategori' => $statistikKategori,
        ];
    }

    /**
     * Kira jumlah tempahan per bulan untuk N bulan ke belakang.
     * Menghormati skop pengguna (staf: sendiri sahaja, admin: semua).
     *
     * Menggunakan SATU query GROUP BY — bukan N query dalam gelung.
     */
    private function kiraTrendBulanan($baseQuery, int $bulan): array
    {
        $namaBulan = ['', 'Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
        $dari = Carbon::now()->subMonths($bulan - 1)->startOfMonth();

        // Satu query: kumpulkan semua bulan dalam julat sekaligus
        // Guna ekspresi DB-agnostik supaya ujian SQLite dan produksi MySQL sama-sama berjalan.
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $selectExpr = $isSqlite
            ? "CAST(strftime('%Y', tarikh) AS INTEGER) AS tahun, CAST(strftime('%m', tarikh) AS INTEGER) AS bulan_num, COUNT(*) AS jumlah"
            : 'YEAR(tarikh) AS tahun, MONTH(tarikh) AS bulan_num, COUNT(*) AS jumlah';
        $groupExpr = $isSqlite
            ? "strftime('%Y', tarikh), strftime('%m', tarikh)"
            : 'YEAR(tarikh), MONTH(tarikh)';

        $baris = (clone $baseQuery)
            ->where('tarikh', '>=', $dari)
            ->selectRaw($selectExpr)
            ->groupByRaw($groupExpr)
            ->get()
            ->keyBy(fn ($r) => $r->tahun.'-'.$r->bulan_num);

        $hasil = [];
        for ($i = $bulan - 1; $i >= 0; $i--) {
            $tarikh = Carbon::now()->subMonths($i);
            $kunci = $tarikh->year.'-'.$tarikh->month;

            $hasil[] = [
                'label' => $namaBulan[$tarikh->month].' '.$tarikh->year,
                // @phpstan-ignore-next-line nullsafe.neverNull — keyBy collection mungkin tiada kunci bulan ini
                'jumlah' => (int) ($baris->get($kunci)?->jumlah ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * Kira jumlah tempahan mengikut kategori untuk bulan tertentu.
     */
    private function kiraKategori($baseQuery, int $bulan, int $tahun): array
    {
        $labelKategori = Tempahan::KATEGORI;

        $rows = (clone $baseQuery)
            ->whereMonth('tarikh', $bulan)
            ->whereYear('tarikh', $tahun)
            ->select('kategori', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kategori')
            ->orderByDesc('jumlah')
            ->get();

        return $rows->map(fn ($r) => [
            'label' => $labelKategori[$r->kategori] ?? $r->kategori,
            'jumlah' => $r->jumlah,
        ])->values()->toArray();
    }

    private function kiraTrend(int $sekarang, int $lepas): array
    {
        if ($lepas > 0) {
            $trend = (int) round((($sekarang - $lepas) / $lepas) * 100);
            $naik = $trend >= 0;
        } elseif ($sekarang > 0) {
            $trend = 100;
            $naik = true;
        } else {
            $trend = 0;
            $naik = true;
        }

        return [$trend, $naik];
    }
}
