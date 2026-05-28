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
 * Kes Tempahan Berulang Lanjut — pratonton AJAX, update (pemilik + bukan pemilik),
 * dan cabang-cabang lain yang belum diliputi.
 */
class TempahanBerulangLanjutTest extends TestCase
{
    // ── Helper ───────────────────────────────────────────────────────

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
            'nama_mesyuarat' => 'Mesyuarat Bulanan',
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah',
        ]);

        for ($i = 1; $i <= $bilangan; $i++) {
            Tempahan::create([
                'ulid' => (string) Str::ulid(),
                'tempahan_berulang_id' => $kumpulan->id,
                'nama_mesyuarat' => 'Mesyuarat Bulanan',
                'tarikh' => now()->addMonths($i)->startOfMonth()->toDateString(),
                'sesi' => 'pagi',
                'masa_mula' => '09:00',
                'masa_tamat' => '13:00',
                'bilik_id' => $bilik->id,
                'user_id' => $pengguna->id,
                'bilangan_peserta' => 10,
                'kategori' => 'mesyuarat',
                'nama_pengerusi' => 'Pengarah',
                'status' => Tempahan::STATUS_DILULUSKAN,
            ]);
        }

        return $kumpulan->refresh();
    }

    // ── pratonton() AJAX ─────────────────────────────────────────────

    #[Test]
    public function pratonton_berulang_bulanan_kembalikan_tarikh_yang_betul(): void
    {
        $staf = User::factory()->staf()->create();

        $url = '/tempahan-berulang/pratonton?'
            .'jenis=bulanan&setiap_n=1'
            .'&tarikh_mula=2027-01-01&tarikh_tamat=2027-03-01';

        $response = $this->actingAs($staf)->get($url);

        $response->assertOk()
            ->assertJsonStructure(['tarikh', 'jumlah', 'had', 'tercapai_had']);

        $data = $response->json();
        // Bulanan: 3 bulan = 3 tarikh (Jan, Feb, Mac)
        $this->assertEquals(3, $data['jumlah']);
    }

    #[Test]
    public function pratonton_berulang_mingguan_kembalikan_tarikh_yang_betul(): void
    {
        $staf = User::factory()->staf()->create();

        // 4 Selasa dalam bulan Jun 2027
        $url = '/tempahan-berulang/pratonton?'
            .'jenis=mingguan&setiap_n=1'
            .'&hari_dalam_minggu[]=2' // Selasa = 2
            .'&tarikh_mula=2027-06-01&tarikh_tamat=2027-06-30';

        $response = $this->actingAs($staf)->get($url);

        $response->assertOk();

        $data = $response->json();
        $this->assertGreaterThan(0, $data['jumlah']);
    }

    #[Test]
    public function pratonton_gagal_jika_tarikh_tamat_sebelum_tarikh_mula(): void
    {
        $staf = User::factory()->staf()->create();

        $url = '/tempahan-berulang/pratonton?'
            .'jenis=bulanan&setiap_n=1'
            .'&tarikh_mula=2027-06-01&tarikh_tamat=2027-01-01'; // tamat sebelum mula

        $response = $this->actingAs($staf)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($url);

        // Validation error — tarikh_tamat mestilah selepas tarikh_mula
        $response->assertUnprocessable();
    }

    #[Test]
    public function pratonton_gagal_jika_jenis_tidak_sah(): void
    {
        $staf = User::factory()->staf()->create();

        $url = '/tempahan-berulang/pratonton?'
            .'jenis=harian&setiap_n=1' // 'harian' tidak sah
            .'&tarikh_mula=2027-01-01&tarikh_tamat=2027-03-01';

        $response = $this->actingAs($staf)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($url);

        $response->assertUnprocessable();
    }

    // ── update() — pemilik boleh kemaskini ───────────────────────────

    #[Test]
    public function pemilik_kumpulan_boleh_kemaskini_tempahan_berulang(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $kumpulan = $this->buatKumpulan($staf, $bilik, 2);

        $response = $this->actingAs($staf)->put("/tempahan-berulang/{$kumpulan->ulid}", [
            'nama_mesyuarat' => 'Nama Baharu Oleh Pemilik',
            'bilangan_peserta' => 20,
            'kategori' => 'taklimat',
            'nama_pengerusi' => 'Ketua Baharu',
        ]);

        $response->assertRedirect(route('tempahan.index'));

        $this->assertDatabaseHas('tempahan_berulang', [
            'id' => $kumpulan->id,
            'nama_mesyuarat' => 'Nama Baharu Oleh Pemilik',
        ]);
    }

    #[Test]
    public function bukan_pemilik_tidak_boleh_kemaskini_kumpulan_berulang(): void
    {
        $pemilik = User::factory()->staf()->create();
        $stafLain = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $kumpulan = $this->buatKumpulan($pemilik, $bilik, 2);

        // Staf lain cuba kemaskini kumpulan orang lain
        $response = $this->actingAs($stafLain)->put("/tempahan-berulang/{$kumpulan->ulid}", [
            'nama_mesyuarat' => 'Cuba Ceroboh',
            'bilangan_peserta' => 5,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Penceroboh',
        ]);

        $response->assertForbidden();

        // Data tidak berubah
        $this->assertDatabaseHas('tempahan_berulang', [
            'id' => $kumpulan->id,
            'nama_mesyuarat' => 'Mesyuarat Bulanan',
        ]);
    }

    #[Test]
    public function update_kumpulan_berulang_gagal_jika_nama_kosong(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 2);

        $response = $this->actingAs($pentadbir)
            ->withHeaders(['Accept' => 'application/json'])
            ->put("/tempahan-berulang/{$kumpulan->ulid}", [
                'nama_mesyuarat' => '', // wajib diisi
                'bilangan_peserta' => 10,
                'kategori' => 'mesyuarat',
                'nama_pengerusi' => 'Pengurus',
            ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function update_kumpulan_kemaskini_semua_tempahan_aktif_sahaja(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $kumpulan = $this->buatKumpulan($pentadbir, $bilik, 3);

        // Tandakan satu tempahan sebagai DITOLAK — tidak patut dikemaskini
        $tempahanDitolak = $kumpulan->tempahan()->first();
        $tempahanDitolak->update(['status' => Tempahan::STATUS_DITOLAK]);

        $this->actingAs($pentadbir)->put("/tempahan-berulang/{$kumpulan->ulid}", [
            'nama_mesyuarat' => 'Nama Kemaskini Aktif',
            'bilangan_peserta' => 15,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah Baru',
        ]);

        // 2 tempahan aktif dikemaskini
        $this->assertEquals(2,
            Tempahan::where('tempahan_berulang_id', $kumpulan->id)
                ->where('nama_mesyuarat', 'Nama Kemaskini Aktif')
                ->count()
        );

        // Tempahan ditolak TIDAK dikemaskini
        $this->assertDatabaseHas('tempahan', [
            'id' => $tempahanDitolak->id,
            'nama_mesyuarat' => 'Mesyuarat Bulanan', // nama asal
        ]);
    }

    // ── store() — cabang pelbagai sesi ──────────────────────────────

    #[Test]
    public function staf_boleh_buat_tempahan_berulang_dua_sesi_serentak(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();

        $mulaBulan = now()->addMonth()->startOfMonth();

        $response = $this->actingAs($staf)->post('/tempahan-berulang', [
            'nama_mesyuarat' => 'Latihan Sepanjang Hari',
            'bilik_id' => $bilik->id,
            'sesi' => ['pagi', 'petang'], // dua sesi
            'bilangan_peserta' => 20,
            'kategori' => 'latihan',
            'nama_pengerusi' => 'Jurulatih',
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'tarikh_mula' => $mulaBulan->format('Y-m-d'),
            'tarikh_tamat' => $mulaBulan->copy()->addMonths(1)->format('Y-m-d'),
        ]);

        $response->assertRedirect('/tempahan?tarikh_filter=akan_datang');

        // 2 bulan × 2 sesi = 4 tempahan
        $kumpulan = TempahanBerulang::where('user_id', $staf->id)->first();
        $this->assertNotNull($kumpulan);
        $this->assertEquals(4, Tempahan::where('tempahan_berulang_id', $kumpulan->id)->count());
    }
}
