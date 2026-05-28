<?php

namespace Tests\Unit;

use App\Models\TempahanBerulang;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit test: TempahanBerulang::janaTarikh()
 *
 * Uji logik penjana tarikh berulang — tiada HTTP request, tiada auth.
 * Hanya uji algoritma corak mingguan dan bulanan.
 */
class JanaTarikhTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mingguan_satu_hari_jana_tarikh_betul_dalam_sebulan(): void
    {
        // Jun 2026: 5 Isnin — 1, 8, 15, 22, 29 Jun
        $berulang = new TempahanBerulang([
            'jenis' => 'mingguan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => [1], // 1 = Isnin
            'tarikh_mula' => '2026-06-01',
            'tarikh_tamat' => '2026-06-30',
            'sesi' => ['pagi'],
        ]);

        $tarikh = $berulang->janaTarikh();

        $this->assertCount(5, $tarikh);
        $this->assertEquals('2026-06-01', $tarikh->first()->toDateString());
        $this->assertEquals('2026-06-29', $tarikh->last()->toDateString());
    }

    #[Test]
    public function mingguan_dua_hari_jana_tarikh_mengikut_urutan(): void
    {
        // Isnin + Khamis, 2 minggu = 4 tarikh: 1, 4, 8, 11 Jun
        $berulang = new TempahanBerulang([
            'jenis' => 'mingguan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => [1, 4], // Isnin + Khamis
            'tarikh_mula' => '2026-06-01',
            'tarikh_tamat' => '2026-06-14',
            'sesi' => ['pagi', 'petang'],
        ]);

        $tarikh = $berulang->janaTarikh();

        $this->assertCount(4, $tarikh);
        $this->assertEquals('2026-06-01', $tarikh->get(0)->toDateString());
        $this->assertEquals('2026-06-04', $tarikh->get(1)->toDateString());
        $this->assertEquals('2026-06-08', $tarikh->get(2)->toDateString());
        $this->assertEquals('2026-06-11', $tarikh->get(3)->toDateString());
    }

    #[Test]
    public function bulanan_jana_12_tarikh_dalam_setahun(): void
    {
        $berulang = new TempahanBerulang([
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'tarikh_mula' => '2026-01-15',
            'tarikh_tamat' => '2026-12-31',
            'sesi' => ['pagi'],
        ]);

        $tarikh = $berulang->janaTarikh();

        $this->assertCount(12, $tarikh);
        $this->assertEquals('2026-01-15', $tarikh->first()->toDateString());
        $this->assertEquals('2026-12-15', $tarikh->last()->toDateString());
    }

    #[Test]
    public function bulanan_setiap_2_bulan_jana_6_tarikh(): void
    {
        // Jan, Mar, Mei, Jul, Sep, Nov 2026 = 6 tarikh
        $berulang = new TempahanBerulang([
            'jenis' => 'bulanan',
            'setiap_n' => 2,
            'tarikh_mula' => '2026-01-01',
            'tarikh_tamat' => '2026-12-31',
            'sesi' => ['pagi'],
        ]);

        $tarikh = $berulang->janaTarikh();

        $this->assertCount(6, $tarikh);
        $this->assertEquals('2026-01-01', $tarikh->first()->toDateString());
        $this->assertEquals('2026-11-01', $tarikh->last()->toDateString());
    }

    #[Test]
    public function had_keras_12_kejadian_dikuatkuasakan_walaupun_lebih_banyak_tersedia(): void
    {
        // Mingguan semua hari dalam seminggu sepanjang setahun → mesti cap pada 12
        $berulang = new TempahanBerulang([
            'jenis' => 'mingguan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => [0, 1, 2, 3, 4, 5, 6], // semua 7 hari
            'tarikh_mula' => '2026-01-01',
            'tarikh_tamat' => '2026-12-31',
            'sesi' => ['pagi'],
        ]);

        $tarikh = $berulang->janaTarikh();

        $this->assertCount(TempahanBerulang::MAX_KEJADIAN, $tarikh);
        $this->assertEquals(12, $tarikh->count());
    }

    #[Test]
    public function tarikh_diluar_julat_tidak_disertakan(): void
    {
        // Hanya 3 hari dalam julat: 1, 2, 3 Jan
        $berulang = new TempahanBerulang([
            'jenis' => 'mingguan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => [1, 2, 3], // Isnin, Selasa, Rabu
            'tarikh_mula' => '2026-01-01', // Khamis — bermula dari sini
            'tarikh_tamat' => '2026-01-07',
            'sesi' => ['pagi'],
        ]);

        $tarikh = $berulang->janaTarikh();

        // Jan 2026: 1=Khamis, 2=Jumaat, 3=Sabtu, 4=Ahad, 5=Isnin, 6=Selasa, 7=Rabu
        // Isnin(5), Selasa(6), Rabu(7) — semua dalam julat
        $this->assertCount(3, $tarikh);
        foreach ($tarikh as $t) {
            $this->assertTrue($t->gte(Carbon::parse('2026-01-01')));
            $this->assertTrue($t->lte(Carbon::parse('2026-01-07')));
        }
    }
}
