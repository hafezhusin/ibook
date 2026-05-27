<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 8 & 9 — Endpoint Awam (Tanpa Pengesahan)
 */
class AwamTest extends TestCase
{
    #[Test]
    public function endpoint_awam_senarai_bilik_boleh_diakses_tanpa_log_masuk(): void
    {
        BilikMesyuarat::factory()->count(3)->create();
        BilikMesyuarat::factory()->nyahaktif()->create(); // tidak sepatutnya muncul

        $response = $this->getJson('/awam/bilik');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'nama', 'kapasiti'],
                 ]);

        // Hanya bilik aktif dikembalikan
        $this->assertCount(3, $response->json());
    }

    #[Test]
    public function endpoint_awam_events_kalendar_boleh_diakses_tanpa_log_masuk(): void
    {
        $response = $this->getJson('/awam/events');

        // Mesti berjaya (200) — bukan redirect ke login
        $response->assertStatus(200);
        $response->assertJsonStructure([]);  // array (mungkin kosong)
    }
}
