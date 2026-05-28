<?php

namespace Tests\Unit;

use App\Filters\TempahanFilter;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit test: TempahanFilter::terapkan()
 *
 * Uji bahawa setiap parameter filter menapis data dengan betul.
 */
class TempahanFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $pentadbir;

    private BilikMesyuarat $bilik;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pentadbir = User::factory()->pentadbir()->create();
        $this->bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
    }

    /** Bina request kosong atau dengan parameter tertentu. */
    private function buatRequest(array $params = []): Request
    {
        return new Request($params);
    }

    /** Jalankan filter dan kembalikan hasil. */
    private function filter(array $params = []): Collection
    {
        $this->actingAs($this->pentadbir);
        $query = Tempahan::query();
        TempahanFilter::terapkan($query, $this->buatRequest($params));

        return $query->get();
    }

    #[Test]
    public function filter_kosong_kembalikan_semua_rekod(): void
    {
        Tempahan::factory()->count(3)->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
        ]);

        $hasil = $this->filter([]);

        $this->assertCount(3, $hasil);
    }

    #[Test]
    public function filter_status_menapis_dengan_betul(): void
    {
        Tempahan::factory()->count(2)->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'status' => Tempahan::STATUS_DITOLAK,
        ]);

        $hasil = $this->filter(['status' => Tempahan::STATUS_DILULUSKAN]);

        $this->assertCount(2, $hasil);
        $hasil->each(fn ($t) => $this->assertEquals(Tempahan::STATUS_DILULUSKAN, $t->status));
    }

    #[Test]
    public function filter_bilik_id_menapis_dengan_betul(): void
    {
        $bilikLain = BilikMesyuarat::factory()->kapasiti(10)->create();

        Tempahan::factory()->count(2)->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $bilikLain->id,
            'user_id' => $this->pentadbir->id,
        ]);

        $hasil = $this->filter(['bilik_id' => $this->bilik->id]);

        $this->assertCount(2, $hasil);
        $hasil->each(fn ($t) => $this->assertEquals($this->bilik->id, $t->bilik_id));
    }

    #[Test]
    public function filter_carian_nama_mesyuarat_menapis_dengan_betul(): void
    {
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'nama_mesyuarat' => 'Mesyuarat Pengurusan Kewangan',
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'nama_mesyuarat' => 'Taklimat ICT 2026',
        ]);

        $hasil = $this->filter(['carian' => 'Kewangan']);

        $this->assertCount(1, $hasil);
        $this->assertStringContainsString('Kewangan', $hasil->first()->nama_mesyuarat);
    }

    #[Test]
    public function filter_tarikh_dari_dan_hingga_menapis_julat_betul(): void
    {
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'tarikh' => '2026-06-10',
            'sesi' => 'pagi',
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'tarikh' => '2026-07-15',
            'sesi' => 'pagi',
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'tarikh' => '2026-08-20',
            'sesi' => 'petang',
        ]);

        $hasil = $this->filter([
            'tarikh_dari' => '2026-06-01',
            'tarikh_hingga' => '2026-07-31',
        ]);

        $this->assertCount(2, $hasil);
    }

    #[Test]
    public function filter_tarikh_filter_hari_ini_betul(): void
    {
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'tarikh' => today()->toDateString(),
            'sesi' => 'pagi',
        ]);
        Tempahan::factory()->create([
            'bilik_id' => $this->bilik->id,
            'user_id' => $this->pentadbir->id,
            'tarikh' => today()->addDays(5)->toDateString(),
            'sesi' => 'pagi',
        ]);

        $hasil = $this->filter(['tarikh_filter' => 'hari_ini']);

        $this->assertCount(1, $hasil);
        $this->assertEquals(today()->toDateString(), $hasil->first()->tarikh->toDateString());
    }
}
