<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Kes 3 & 4 — Kawalan Akses Berdasarkan Peranan (RBAC)
 */
class RbacTest extends TestCase
{
    /** @test */
    public function staf_tidak_boleh_akses_pengurusan_bilik_mesyuarat(): void
    {
        $staf = User::factory()->staf()->create();

        $this->actingAs($staf)
            ->get('/bilik-mesyuarat')
            ->assertStatus(403);
    }

    /** @test */
    public function staf_tidak_boleh_akses_pengurusan_pengguna(): void
    {
        $staf = User::factory()->staf()->create();

        $this->actingAs($staf)
            ->get('/pengguna')
            ->assertStatus(403);
    }

    /** @test */
    public function pentadbir_boleh_akses_pengurusan_bilik_mesyuarat(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $this->actingAs($pentadbir)
            ->get('/bilik-mesyuarat')
            ->assertStatus(200);
    }

    /** @test */
    public function tetamu_tanpa_log_masuk_dihalang_dari_dashboard(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }
}
