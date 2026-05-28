<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\TempahanBerulang;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 11 — Tempahan Berulang (fitur utama)
 *
 * Meliputi: store (buat kumpulan), update (kemaskini semua),
 * destroy skop=ini (padam satu), destroy skop=semua (padam kumpulan penuh).
 */
class TempahanBerulangTest extends TestCase
{
    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Cipta TempahanBerulang dan beberapa Tempahan individu berkaitan.
     * Digunakan oleh test update dan destroy.
     */
    private function buatKumpulan(User $pengguna, BilikMesyuarat $bilik, int $bilangan = 3): TempahanBerulang
    {
        $kumpulan = TempahanBerulang::create([
            'ulid' => (string) Str::ulid(),
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => null,
            'tarikh_mula' => now()->addMonth()->startOfMonth()->toDateString(),
            'tarikh_tamat' => now()->addMonths($bilangan)->startOfMonth()->toDateString(),
            'sesi' => ['pagi'],
            'bilik_id' => $bilik->id,
            'user_id' => $pengguna->id,
            'nama_mesyuarat' => 'Mesyuarat Pengurusan Bulanan',
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah BPTM',
        ]);

        for ($i = 1; $i <= $bilangan; $i++) {
            Tempahan::create([
                'ulid' => (string) Str::ulid(),
                'tempahan_berulang_id' => $kumpulan->id,
                'nama_mesyuarat' => 'Mesyuarat Pengurusan Bulanan',
                'tarikh' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'sesi' => 'pagi',
                'masa_mula' => '09:00',
                'masa_tamat' => '13:00',
                'bilik_id' => $bilik->id,
                'user_id' => $pengguna->id,
                'bilangan_peserta' => 10,
                'kategori' => 'mesyuarat',
                'nama_pengerusi' => 'Pengarah BPTM',
                'status' => Tempahan::STATUS_DILULUSKAN,
            ]);
        }

        return $kumpulan->refresh();
    }

    // ── STORE ────────────────────────────────────────────────────────

    #[Test]
    public function staf_boleh_buat_tempahan_berulang_bulanan(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();

        $mulaBulan = now()->addMonth()->startOfMonth();

        $response = $this->actingAs($staf)->post('/tempahan-berulang', [
            'nama_mesyuarat' => 'Mesyuarat Pengurusan Q3',
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 20,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah Bahagian',
            'tujuan' => 'Semakan prestasi suku tahun',
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'tarikh_mula' => $mulaBulan->format('Y-m-d'),
            'tarikh_tamat' => $mulaBulan->copy()->addMonths(2)->format('Y-m-d'),
        ]);

        // Redirect ke senarai dengan filter akan_datang
        $response->assertRedirect('/tempahan?tarikh_filter=akan_datang');

        // Rekod kumpulan wujud
        $this->assertDatabaseHas('tempahan_berulang', [
            'jenis' => 'bulanan',
            'nama_mesyuarat' => 'Mesyuarat Pengurusan Q3',
            'user_id' => $staf->id,
            'bilik_id' => $bilik->id,
        ]);

        // 3 tempahan individu dijana (bulan 1, 2, 3)
        $kumpulan = TempahanBerulang::where('user_id', $staf->id)->first();
        $this->assertNotNull($kumpulan);
        $this->assertDatabaseCount('tempahan', 3);
    }

    #[Test]
    public function staf_boleh_buat_tempahan_berulang_mingguan(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();

        // Cari Isnin akan datang
        $isnin = now()->next('Monday');

        $response = $this->actingAs($staf)->post('/tempahan-berulang', [
            'nama_mesyuarat' => 'Taklimat Mingguan Unit',
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 15,
            'kategori' => 'taklimat',
            'nama_pengerusi' => 'Ketua Unit',
            'jenis' => 'mingguan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => [1], // Isnin = 1
            'tarikh_mula' => $isnin->format('Y-m-d'),
            'tarikh_tamat' => $isnin->copy()->addWeeks(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect('/tempahan?tarikh_filter=akan_datang');

        // 4 Isnin dalam 4 minggu
        $kumpulan = TempahanBerulang::where('user_id', $staf->id)->first();
        $this->assertNotNull($kumpulan);
        $this->assertEquals(4, Tempahan::where('tempahan_berulang_id', $kumpulan->id)->count());
    }

    #[Test]
    public function store_gagal_jika_konflik_wujud_pada_slot_berulang(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $mula = now()->addMonth()->startOfMonth();

        // Tempah manual pada bulan pertama — ini akan jadi konflik
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $mula->format('Y-m-d'),
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)->post('/tempahan-berulang', [
            'nama_mesyuarat' => 'Mesyuarat Konflik Berulang',
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengurus',
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'tarikh_mula' => $mula->format('Y-m-d'),
            'tarikh_tamat' => $mula->copy()->addMonths(2)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('tarikh_mula');
        $this->assertDatabaseMissing('tempahan_berulang', ['nama_mesyuarat' => 'Mesyuarat Konflik Berulang']);
    }

    #[Test]
    public function store_gagal_jika_tarikh_mula_sudah_lepas(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $response = $this->actingAs($staf)->post('/tempahan-berulang', [
            'nama_mesyuarat' => 'Tempahan Lepas',
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi'],
            'bilangan_peserta' => 5,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengurus',
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'tarikh_mula' => now()->subMonth()->format('Y-m-d'), // lepas!
            'tarikh_tamat' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('tarikh_mula');
        $this->assertDatabaseMissing('tempahan_berulang', ['nama_mesyuarat' => 'Tempahan Lepas']);
    }

    // ── UPDATE ───────────────────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_kemaskini_semua_tempahan_dalam_kumpulan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 3);

        $response = $this->actingAs($pentadbir)->put("/tempahan-berulang/{$kumpulan->ulid}", [
            'nama_mesyuarat' => 'Mesyuarat Pengurusan DIKEMASKINI',
            'bilangan_peserta' => 15,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah Baru',
        ]);

        $response->assertRedirect('/tempahan');

        // Kumpulan dikemaskini
        $this->assertDatabaseHas('tempahan_berulang', [
            'id' => $kumpulan->id,
            'nama_mesyuarat' => 'Mesyuarat Pengurusan DIKEMASKINI',
        ]);

        // Semua 3 tempahan dikemaskini
        $this->assertEquals(3,
            Tempahan::where('tempahan_berulang_id', $kumpulan->id)
                ->where('nama_mesyuarat', 'Mesyuarat Pengurusan DIKEMASKINI')
                ->count()
        );
    }

    // ── DESTROY ──────────────────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_padam_satu_tempahan_dalam_kumpulan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 3);

        $satTempahan = $kumpulan->tempahan()->first();

        $response = $this->actingAs($pentadbir)->delete(
            "/tempahan/{$satTempahan->ulid}/padam-berulang",
            ['skop' => 'ini']
        );

        $response->assertRedirect('/tempahan');

        // Hanya 1 yang dipadam, kumpulan masih ada dengan 2 tempahan
        $this->assertDatabaseMissing('tempahan', ['id' => $satTempahan->id]);
        $this->assertDatabaseHas('tempahan_berulang', ['id' => $kumpulan->id]);
        $this->assertEquals(2, Tempahan::where('tempahan_berulang_id', $kumpulan->id)->count());
    }

    #[Test]
    public function pentadbir_boleh_padam_semua_tempahan_dalam_kumpulan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 3);

        $satTempahan = $kumpulan->tempahan()->first();

        $response = $this->actingAs($pentadbir)->delete(
            "/tempahan/{$satTempahan->ulid}/padam-berulang",
            ['skop' => 'semua']
        );

        $response->assertRedirect('/tempahan');

        // Semua tempahan dan kumpulan dipadam
        $this->assertEquals(0, Tempahan::where('tempahan_berulang_id', $kumpulan->id)->count());
        $this->assertDatabaseMissing('tempahan_berulang', ['id' => $kumpulan->id]);
    }

    #[Test]
    public function staf_tidak_boleh_padam_tempahan_berulang(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $kumpulan = $this->buatKumpulan($staf, $bilik, 2);

        $satTempahan = $kumpulan->tempahan()->first();

        $response = $this->actingAs($staf)->delete(
            "/tempahan/{$satTempahan->ulid}/padam-berulang",
            ['skop' => 'semua']
        );

        // Staf tidak dibenarkan padam (policy.delete = isPentadbir)
        $response->assertForbidden();
        $this->assertEquals(2, Tempahan::where('tempahan_berulang_id', $kumpulan->id)->count());
    }

    #[Test]
    public function padam_tempahan_terakhir_dalam_kumpulan_padam_kumpulan_juga(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 1); // 1 tempahan sahaja

        $satTempahan = $kumpulan->tempahan()->first();

        $this->actingAs($pentadbir)->delete(
            "/tempahan/{$satTempahan->ulid}/padam-berulang",
            ['skop' => 'ini']
        );

        // Tempahan dipadam, kumpulan juga dipadam (sebab kosong)
        $this->assertDatabaseMissing('tempahan', ['id' => $satTempahan->id]);
        $this->assertDatabaseMissing('tempahan_berulang', ['id' => $kumpulan->id]);
    }
}
