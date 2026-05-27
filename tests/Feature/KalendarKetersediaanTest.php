<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 13 — Kalendar & Semak Ketersediaan
 */
class KalendarKetersediaanTest extends TestCase
{
    // ── KalendarController ───────────────────────────────────────────

    #[Test]
    public function pengguna_log_masuk_boleh_akses_halaman_kalendar(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/kalendar');

        $response->assertOk();
    }

    #[Test]
    public function events_api_kembalikan_json_untuk_pengguna_log_masuk(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        Tempahan::factory()->pagi()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
            'status'   => Tempahan::STATUS_DILULUSKAN,
        ]);

        $mula  = now()->startOfMonth()->format('Y-m-d');
        $akhir = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($staf)
            ->get("/kalendar/events?start={$mula}&end={$akhir}");

        $response->assertOk()
            ->assertJsonStructure([['id', 'title', 'start', 'end']]);
    }

    // ── KetersediaanController ────────────────────────────────────────

    #[Test]
    public function pengguna_log_masuk_boleh_akses_halaman_semak_ketersediaan(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/semak-bilik');

        $response->assertOk();
    }

    #[Test]
    public function cek_ketersediaan_kembalikan_status_bilik(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        // Bilik ini ada tempahan sesi pagi
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh'   => $tarikh,
            'status'   => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)
            ->get("/semak-bilik/cek?tarikh={$tarikh}&sesi=semua&peserta=10");

        // cek() membalut hasil dalam kunci 'bilik', bukan array peringkat atas terus.
        // Setiap bilik mempunyai 'status_sesi' (bukan 'sesi') untuk status pagi/petang.
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
    public function cek_ketersediaan_gagal_jika_tarikh_tidak_diisi(): void
    {
        $staf = User::factory()->staf()->create();

        // Tanpa Accept: application/json, Laravel redirect 302 bukan 422.
        // Tambah header JSON supaya validation exception dibalut sebagai 422.
        $response = $this->actingAs($staf)
            ->withHeaders(['Accept' => 'application/json'])
            ->get('/semak-bilik/cek');

        $response->assertUnprocessable(); // 422
    }
}
