<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 10 — Kebenaran Eksport Laporan
 */
class EksportTest extends TestCase
{
    #[Test]
    public function hanya_pentadbir_boleh_eksport_pdf(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf      = User::factory()->staf()->create();

        // Pentadbir — dibenarkan
        $this->actingAs($pentadbir)
            ->get('/tempahan/eksport/pdf')
            ->assertStatus(200);

        // Staf — dihalang (403 atau redirect dengan error)
        $responsStaf = $this->actingAs($staf)
            ->get('/tempahan/eksport/pdf');

        $responsStaf->assertStatus(403);
    }

    #[Test]
    public function hanya_pentadbir_boleh_eksport_excel(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf      = User::factory()->staf()->create();

        // Pentadbir — dibenarkan
        $this->actingAs($pentadbir)
            ->get('/tempahan/eksport/excel')
            ->assertStatus(200);

        // Staf — dihalang
        $this->actingAs($staf)
            ->get('/tempahan/eksport/excel')
            ->assertStatus(403);
    }
}
