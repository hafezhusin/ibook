<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes Ketersediaan Lanjut — KetersediaanController: index, cek (pelbagai sesi), minggu
 */
class KetersediaanLanjutTest extends TestCase
{
    // ── cek() — semak ketersediaan sesi tertentu ─────────────────────

    #[Test]
    public function cek_ketersediaan_sesi_pagi_sahaja(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $tarikh = now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=pagi&peserta=10");

        $response->assertOk()
            ->assertJsonStructure([
                'tarikh',
                'sesi',
                'peserta',
                'bilik' => [
                    '*' => ['id', 'nama', 'kapasiti', 'status_sesi', 'boleh_tempah'],
                ],
            ]);
    }

    #[Test]
    public function cek_ketersediaan_sesi_petang_sahaja(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(15)->create();
        $tarikh = now()->addDays(5)->format('Y-m-d');

        // Tempah sesi pagi — petang masih tersedia
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $tarikh,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=petang&peserta=10");

        $response->assertOk();

        // Sesi petang bilik ini masih tersedia
        $data = $response->json();
        $bilikData = collect($data['bilik'])->firstWhere('id', $bilik->id);
        $this->assertTrue($bilikData['status_sesi']['petang'] ?? false);
    }

    #[Test]
    public function cek_ketersediaan_kembalikan_data_bilik_mengikut_kapasiti(): void
    {
        $staf = User::factory()->staf()->create();
        // Bilik kecil (5 tempat)
        $bilikKecil = BilikMesyuarat::factory()->kapasiti(5)->create();
        // Bilik besar (50 tempat)
        $bilikBesar = BilikMesyuarat::factory()->kapasiti(50)->create();
        $tarikh = now()->addDays(90)->format('Y-m-d');

        // Minta 30 peserta — bilik kecil tidak mencukupi
        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=pagi&peserta=30");

        $response->assertOk();

        $data = $response->json();

        // Bilik kecil: kapasiti_cukup = false
        $bilikKecilData = collect($data['bilik'])->firstWhere('id', $bilikKecil->id);
        $this->assertNotNull($bilikKecilData);
        $this->assertFalse($bilikKecilData['kapasiti_cukup']);
        $this->assertFalse($bilikKecilData['boleh_tempah']); // tidak boleh tempah — kapasiti tak cukup

        // Bilik besar: kapasiti_cukup = true
        $bilikBesarData = collect($data['bilik'])->firstWhere('id', $bilikBesar->id);
        $this->assertNotNull($bilikBesarData);
        $this->assertTrue($bilikBesarData['kapasiti_cukup']);
        $this->assertTrue($bilikBesarData['boleh_tempah']); // boleh tempah — kapasiti cukup, tiada tempahan
    }

    #[Test]
    public function cek_ketersediaan_kapasiti_tidak_cukup_tandakan_tidak_boleh_tempah(): void
    {
        $staf = User::factory()->staf()->create();
        // Bilik dengan kapasiti 5 sahaja
        BilikMesyuarat::factory()->kapasiti(5)->create();
        $tarikh = now()->addDays(4)->format('Y-m-d');

        // Minta 50 peserta — melebihi kapasiti
        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=pagi&peserta=50");

        $response->assertOk();

        $data = $response->json();
        // Semua bilik dengan kapasiti < 50 tidak boleh ditempah
        foreach ($data['bilik'] as $b) {
            if ($b['kapasiti'] < 50) {
                $this->assertFalse($b['boleh_tempah']);
            }
        }
    }

    #[Test]
    public function cek_ketersediaan_tempahan_ditolak_tidak_menghalang_slot(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $tarikh = now()->addDays(6)->format('Y-m-d');

        // Tempahan DITOLAK — tidak patut menghalang slot
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $tarikh,
            'status' => Tempahan::STATUS_DITOLAK,
        ]);

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=pagi&peserta=10");

        $response->assertOk();

        $data = $response->json();
        $bilikData = collect($data['bilik'])->firstWhere('id', $bilik->id);

        // Sesi pagi masih tersedia kerana tempahan ditolak
        $this->assertTrue($bilikData['status_sesi']['pagi'] ?? false);
    }

    // ── minggu() — jadual ketersediaan seminggu ───────────────────────

    #[Test]
    public function minggu_kembalikan_struktur_json_lengkap(): void
    {
        $staf = User::factory()->staf()->create();
        BilikMesyuarat::factory()->kapasiti(20)->create();

        $tarikh = now()->startOfWeek()->format('Y-m-d');

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/minggu?tarikh_mula={$tarikh}&peserta=10");

        $response->assertOk()
            ->assertJsonStructure([
                'tarikh_mula',
                'tarikh_tamat',
                'hari',
                'peserta',
                'bilik' => [
                    '*' => ['id', 'nama', 'kapasiti', 'kapasiti_cukup', 'slot'],
                ],
            ]);
    }

    #[Test]
    public function minggu_tanpa_parameter_guna_minggu_semasa(): void
    {
        $staf = User::factory()->staf()->create();
        BilikMesyuarat::factory()->kapasiti(20)->create();

        $response = $this->actingAs($staf)->get('/semak-bilik/minggu');

        $response->assertOk();

        $data = $response->json();
        // 7 hari dalam seminggu
        $this->assertCount(7, $data['hari']);
    }

    #[Test]
    public function minggu_petanda_slot_ditempah_dengan_betul(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        // Isnin minggu ini
        $isnin = now()->startOfWeek()->format('Y-m-d');

        // Tempah sesi pagi pada Isnin
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $isnin,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/minggu?tarikh_mula={$isnin}");

        $response->assertOk();

        $data = $response->json();
        $bilikData = collect($data['bilik'])->firstWhere('id', $bilik->id);

        // Sesi pagi Isnin tidak tersedia
        $this->assertFalse($bilikData['slot'][$isnin]['pagi']);
        // Sesi petang Isnin masih tersedia
        $this->assertTrue($bilikData['slot'][$isnin]['petang']);
    }
}
