<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 5, 6 & 7 — Aliran Tempahan (Booking Flow)
 */
class TempahanTest extends TestCase
{
    #[Test]
    public function staf_boleh_buat_tempahan_baru_yang_sah(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();

        $response = $this->actingAs($staf)->post('/tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Unit Mei 2026',
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 20,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Encik Ahmad',
            'tujuan' => 'Perbincangan bajet',
        ]);

        // Controller redirect ke senarai dengan filter baharu supaya pengguna nampak tempahan baru
        $response->assertRedirect('/tempahan?tarikh_filter=baharu');
        $this->assertDatabaseHas('tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Unit Mei 2026',
            'bilik_id' => $bilik->id,
            'sesi' => 'pagi',
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);
    }

    #[Test]
    public function tempahan_ditolak_apabila_sesi_sudah_ditempah_bilik_sama(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        // Cipta tempahan sedia ada untuk sesi pagi
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $tarikh,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        // Cuba tempah sesi yang sama
        $response = $this->actingAs($staf)->post('/tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Konflik',
            'tarikh' => $tarikh,
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Encik Ali',
        ]);

        // Mesti gagal dengan error konflik
        $response->assertSessionHasErrors('sesi');
        $this->assertDatabaseMissing('tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Konflik',
        ]);
    }

    #[Test]
    public function tempahan_ditolak_apabila_bilangan_peserta_melebihi_kapasiti(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(10)->create(); // kapasiti 10 orang

        $response = $this->actingAs($staf)->post('/tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Terlalu Ramai',
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 50, // melebihi kapasiti
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Puan Siti',
        ]);

        $response->assertSessionHasErrors('bilangan_peserta');
        $this->assertDatabaseMissing('tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Terlalu Ramai',
        ]);
    }

    #[Test]
    public function tempahan_ditolak_tidak_menghalang_sesi_lain_pada_hari_sama(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        // Tempahan sesi pagi sedia ada
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $tarikh,
            'status' => Tempahan::STATUS_DITOLAK, // DITOLAK — tidak kira sebagai konflik
        ]);

        // Sesi pagi masih boleh ditempah semula (status ditolak diabaikan)
        $response = $this->actingAs($staf)->post('/tempahan', [
            'nama_mesyuarat' => 'Tempahan Selepas Tolak',
            'tarikh' => $tarikh,
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Encik Zul',
        ]);

        $response->assertRedirect('/tempahan?tarikh_filter=baharu');
        $this->assertDatabaseHas('tempahan', [
            'nama_mesyuarat' => 'Tempahan Selepas Tolak',
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);
    }

    #[Test]
    public function staf_tidak_boleh_tempah_tarikh_yang_telah_lepas(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $response = $this->actingAs($staf)->post('/tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Masa Lalu',
            'tarikh' => now()->subDay()->format('Y-m-d'),
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'kategori' => 'taklimat',
            'nama_pengerusi' => 'Encik Razak',
        ]);

        $response->assertSessionHasErrors('tarikh');
        $this->assertDatabaseMissing('tempahan', [
            'nama_mesyuarat' => 'Mesyuarat Masa Lalu',
        ]);
    }
}
