<?php

namespace App\Services;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Kunci cache unik bagi setiap pengguna & peranan.
     * Staf mendapat data unit sendiri, admin/urus setia mendapat semua.
     */
    private function kunciCache(User $user): string
    {
        // Sertakan versi supaya luputkanSemuaCache() benar-benar buang semua entri cache.
        // Setiap kali versi dinaikkan, kunci berubah → cache lama diabaikan secara automatik.
        $versi = Cache::get('dashboard.cache.version', 1);
        $skop  = $user->isStaf() ? "staf.{$user->id}" : "admin.{$user->peranan}";
        return "dashboard.v{$versi}.{$skop}";
    }

    /**
     * Dapatkan semua data dashboard.
     * Hasil di-cache mengikut TTL dalam config/ibook.php.
     */
    public function getData(User $user): array
    {
        $ttl = config('ibook.cache.dashboard', 300);

        return Cache::remember($this->kunciCache($user), $ttl, function () use ($user) {
            return $this->kiraData($user);
        });
    }

    /**
     * Padam cache dashboard untuk pengguna tertentu.
     * Dipanggil bila ada tempahan baru / dikemaskini.
     */
    public function luputkanCache(User $user): void
    {
        Cache::forget($this->kunciCache($user));
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
    private function kiraData(User $user): array
    {
        $bulanIni   = now()->month;
        $tahunIni   = now()->year;
        $bulanLepas = now()->subMonth()->month;
        $tahunLepas = now()->subMonth()->year;

        // Query asas mengikut skop pengguna
        $query = Tempahan::query();
        if ($user->isStaf()) {
            $query->where('user_id', $user->id);
        }

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

        // Statistik bilik
        $bilik              = BilikMesyuarat::where('status', 'aktif')->get();
        $bilikDitempahPagi  = Tempahan::whereDate('tarikh', today())->where('sesi', 'pagi')->where('status', Tempahan::STATUS_DILULUSKAN)->pluck('bilik_id');
        $bilikDitempahPetang = Tempahan::whereDate('tarikh', today())->where('sesi', 'petang')->where('status', Tempahan::STATUS_DILULUSKAN)->pluck('bilik_id');
        $bilikPenuh         = $bilikDitempahPagi->intersect($bilikDitempahPetang);

        $jumlahBilikAktif    = $bilik->count();
        $jumlahBilikTersedia = max(0, $jumlahBilikAktif - $bilikPenuh->count());

        // Kadar penggunaan purata bulan ini
        $kadarPenggunaan = 0;
        if ($bilik->count() > 0) {
            $total = $bilik->sum(fn ($b) => $b->penggunaan_bulan_ini);
            $kadarPenggunaan = (int) round($total / $bilik->count());
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

        // Penggunaan bilik untuk bar chart
        $penggunaanBilik = $bilik->map(fn ($b) => [
            'nama'      => $b->nama,
            'peratusan' => $b->penggunaan_bulan_ini,
        ]);

        // Ketersediaan bilik hari ini — pagi & petang (guna data yang dah dikira, tiada query baru)
        $ketersediaanHariIni = $bilik->map(fn ($b) => [
            'nama'     => $b->nama,
            'kapasiti' => $b->kapasiti,
            'pagi'     => !$bilikDitempahPagi->contains($b->id),
            'petang'   => !$bilikDitempahPetang->contains($b->id),
        ]);

        return [
            'jumlahTempahan'      => $jumlahTempahan,
            'jumlahTempahanLepas' => $jumlahTempahanLepas,
            'trend'               => $trend,
            'trendNaik'           => $trendNaik,
            'mesyuaratHariIni'    => $mesyuaratHariIni,
            'jumlahBilikTersedia' => $jumlahBilikTersedia,
            'jumlahBilikAktif'    => $jumlahBilikAktif,
            'bilikAdaSesiKosong'  => $bilik->filter(fn ($b) => !$bilikPenuh->contains($b->id))->count(),
            'kadarPenggunaan'     => $kadarPenggunaan,
            'mesyuaratAkanDatang'  => $mesyuaratAkanDatang,
            'penggunaanBilik'      => $penggunaanBilik,
            'ketersediaanHariIni'  => $ketersediaanHariIni,
            'bulanIni'             => $bulanIni,
            'tahunIni'            => $tahunIni,
            'bulanLepas'          => $bulanLepas,
            'tahunLepas'          => $tahunLepas,
        ];
    }

    private function kiraTrend(int $sekarang, int $lepas): array
    {
        if ($lepas > 0) {
            $trend   = (int) round((($sekarang - $lepas) / $lepas) * 100);
            $naik    = $trend >= 0;
        } elseif ($sekarang > 0) {
            $trend = 100;
            $naik  = true;
        } else {
            $trend = 0;
            $naik  = true;
        }

        return [$trend, $naik];
    }
}
