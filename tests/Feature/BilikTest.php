<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 8 — Pengurusan Bilik Mesyuarat (CRUD + RBAC)
 */
class BilikTest extends TestCase
{
    #[Test]
    public function pentadbir_boleh_akses_senarai_bilik(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat');

        $response->assertOk();
    }

    #[Test]
    public function staf_tidak_boleh_akses_senarai_bilik(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/bilik-mesyuarat');

        $response->assertForbidden();
    }

    #[Test]
    public function pentadbir_boleh_tambah_bilik_baharu(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/bilik-mesyuarat', [
            'nama'      => 'Bilik Seminar Utama',
            'kapasiti'  => 40,
            'lokasi'    => 'Tingkat 3',
            'kemudahan' => ['Projektor', 'Papan Putih'],
            'status'    => 'aktif',
        ]);

        $response->assertRedirect('/bilik-mesyuarat');
        $this->assertDatabaseHas('bilik_mesyuarat', [
            'nama'     => 'Bilik Seminar Utama',
            'kapasiti' => 40,
            'status'   => 'aktif',
        ]);
    }

    #[Test]
    public function tambah_bilik_gagal_jika_nama_kosong(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/bilik-mesyuarat', [
            'nama'     => '',
            'kapasiti' => 20,
            'status'   => 'aktif',
        ]);

        $response->assertSessionHasErrors('nama');
        $this->assertDatabaseMissing('bilik_mesyuarat', ['kapasiti' => 20]);
    }

    #[Test]
    public function pentadbir_boleh_kemaskini_bilik(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik     = BilikMesyuarat::factory()->create(['nama' => 'Bilik Lama']);

        $response = $this->actingAs($pentadbir)->put("/bilik-mesyuarat/{$bilik->ulid}", [
            'nama'     => 'Bilik Baharu',
            'kapasiti' => 25,
            'status'   => 'aktif',
        ]);

        $response->assertRedirect('/bilik-mesyuarat');
        $this->assertDatabaseHas('bilik_mesyuarat', [
            'id'   => $bilik->id,
            'nama' => 'Bilik Baharu',
        ]);
    }

    #[Test]
    public function pentadbir_boleh_padam_bilik_tanpa_tempahan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik     = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($pentadbir)->delete("/bilik-mesyuarat/{$bilik->ulid}");

        $response->assertRedirect('/bilik-mesyuarat');
        $this->assertSoftDeleted('bilik_mesyuarat', ['id' => $bilik->id]);
    }

    #[Test]
    public function padam_bilik_disekat_jika_ada_rekod_tempahan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik     = BilikMesyuarat::factory()->create();

        // Cipta tempahan untuk bilik ini
        Tempahan::factory()->create([
            'bilik_id' => $bilik->id,
            'user_id'  => $pentadbir->id,
        ]);

        $response = $this->actingAs($pentadbir)->delete("/bilik-mesyuarat/{$bilik->ulid}");

        $response->assertRedirect(); // back() dengan error
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bilik_mesyuarat', ['id' => $bilik->id]);
    }
}
