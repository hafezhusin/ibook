<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 9 — Papar, Edit & Kemaskini Tempahan
 */
class TempahanShowEditTest extends TestCase
{
    #[Test]
    public function staf_boleh_lihat_senarai_tempahan_sendiri(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        Tempahan::factory()->pagi()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf)->get('/tempahan');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_boleh_lihat_senarai_semua_tempahan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf      = User::factory()->staf()->create();
        $bilik     = BilikMesyuarat::factory()->create();

        Tempahan::factory()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
        ]);

        $response = $this->actingAs($pentadbir)->get('/tempahan');

        $response->assertOk();
    }

    #[Test]
    public function staf_boleh_lihat_detail_tempahan_sendiri(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf)->get("/tempahan/{$tempahan->ulid}");

        $response->assertOk();
    }

    #[Test]
    public function staf_boleh_lihat_tempahan_staf_lain_walaupun_beza_unit(): void
    {
        // Policy view() mengembalikan true untuk semua pengguna log masuk —
        // konsisten dengan kalendar awam yang memaparkan semua tempahan.
        $staf1 = User::factory()->staf()->create(['jabatan' => 'Unit A']);
        $staf2 = User::factory()->staf()->create(['jabatan' => 'Unit B']);
        $bilik  = BilikMesyuarat::factory()->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id'  => $staf2->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf1)->get("/tempahan/{$tempahan->ulid}");

        $response->assertOk();
    }

    #[Test]
    public function staf_boleh_lihat_tempahan_rakan_seunit(): void
    {
        $unitSama = 'Unit Aplikasi Gunasama';
        $staf1    = User::factory()->staf()->create(['jabatan' => $unitSama]);
        $staf2    = User::factory()->staf()->create(['jabatan' => $unitSama]);
        $bilik    = BilikMesyuarat::factory()->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id'  => $staf2->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf1)->get("/tempahan/{$tempahan->ulid}");

        $response->assertOk();
    }

    #[Test]
    public function staf_boleh_akses_form_edit_tempahan_sendiri(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf)->get("/tempahan/{$tempahan->ulid}/edit");

        $response->assertOk();
    }

    #[Test]
    public function cek_konflik_api_kembalikan_status_ketersediaan_sesi(): void
    {
        $staf  = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        // Tempah sesi pagi
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh'   => $tarikh,
            'status'   => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)->get("/tempahan/cek-konflik?bilik_id={$bilik->id}&tarikh={$tarikh}");

        $response->assertOk()
            ->assertJson([
                'pagi'     => true,   // sudah ditempah
                'petang'   => false,  // masih kosong
                'kapasiti' => $bilik->kapasiti,
            ]);
    }
}
