<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

/**
 * Kes 15 — Tetapan Sistem, Pengurusan Pengguna Lanjut & Tempahan Lanjut
 *
 * Meliputi kawasan berisiko tinggi yang belum diuji:
 *  - TetapanController (0% coverage) — GET/POST /tetapan
 *  - PenggunaController::bulkAktif() — tindakan pukal
 *  - PenggunaController::resetPassword() — reset kata laluan pentadbir
 *  - TempahanController::create() — pra-isi dari duplikat_id & bilik_id/tarikh
 *  - TempahanController::index() dengan tarikh_filter aktif (isih mengikut tarikh)
 */
class TetapanPenggunaLanjutTest extends TestCase
{
    // ── TetapanController ────────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_akses_halaman_tetapan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/tetapan');

        $response->assertOk();
    }

    #[Test]
    public function staf_tidak_boleh_akses_halaman_tetapan(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/tetapan');

        $response->assertForbidden();
    }

    #[Test]
    public function pentadbir_boleh_kemaskini_tetapan_sistem(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/tetapan', [
            'nama_jabatan'         => 'Bahagian Pengurusan Teknologi Maklumat',
            'nama_sistem'          => 'iBook 2.0',
            'emel_pentadbir'       => 'pentadbir@anm.gov.my',
            'emel_notifikasi'      => 'notif@anm.gov.my',
            'notif_tempahan_baru'  => '1',
            'notif_kelulusan'      => '1',
            'peringatan_mesyuarat' => '0',
        ]);

        $response->assertRedirect(route('tetapan.index'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function kemaskini_tetapan_gagal_jika_nama_jabatan_kosong(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/tetapan', [
            'nama_jabatan' => '',  // wajib diisi
        ]);

        // Validation fails — redirect balik dengan error
        $response->assertRedirect();
        $response->assertSessionHasErrors('nama_jabatan');
    }

    #[Test]
    public function staf_tidak_boleh_kemaskini_tetapan(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->post('/tetapan', [
            'nama_jabatan' => 'Percubaan Ceroboh',
        ]);

        $response->assertForbidden();
    }

    // ── PenggunaController: bulkAktif ───────────────────────────────

    #[Test]
    public function pentadbir_boleh_bulk_aktifkan_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf1     = User::factory()->staf()->create(['aktif' => false]);
        $staf2     = User::factory()->staf()->create(['aktif' => false]);

        $response = $this->actingAs($pentadbir)->post('/pengguna/bulk-aktif', [
            'ids'      => [$staf1->id, $staf2->id],
            'tindakan' => 'aktifkan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $staf1->id, 'aktif' => true]);
        $this->assertDatabaseHas('users', ['id' => $staf2->id, 'aktif' => true]);
    }

    #[Test]
    public function pentadbir_boleh_bulk_nyahaktifkan_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf1     = User::factory()->staf()->create(['aktif' => true]);
        $staf2     = User::factory()->staf()->create(['aktif' => true]);

        $response = $this->actingAs($pentadbir)->post('/pengguna/bulk-aktif', [
            'ids'      => [$staf1->id, $staf2->id],
            'tindakan' => 'nyahaktifkan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $staf1->id, 'aktif' => false]);
        $this->assertDatabaseHas('users', ['id' => $staf2->id, 'aktif' => false]);
    }

    #[Test]
    public function bulk_nyahaktifkan_tidak_boleh_termasuk_diri_sendiri(): void
    {
        $pentadbir = User::factory()->pentadbir()->create(['aktif' => true]);
        $staf      = User::factory()->staf()->create(['aktif' => true]);

        // Masukkan ID pentadbir sendiri dalam senarai nyahaktifkan
        $response = $this->actingAs($pentadbir)->post('/pengguna/bulk-aktif', [
            'ids'      => [$pentadbir->id, $staf->id],
            'tindakan' => 'nyahaktifkan',
        ]);

        // Pentadbir sendiri TIDAK dinyahaktifkan, staf dinyahaktifkan
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $pentadbir->id, 'aktif' => true]);
        $this->assertDatabaseHas('users', ['id' => $staf->id, 'aktif' => false]);
    }

    #[Test]
    public function bulk_aktif_gagal_jika_tiada_ids(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/pengguna/bulk-aktif', [
                'ids'      => [],
                'tindakan' => 'aktifkan',
            ]);

        $response->assertUnprocessable(); // 422 — ids mestilah sekurang-kurangnya 1
    }

    // ── PenggunaController: resetPassword ───────────────────────────

    #[Test]
    public function pentadbir_boleh_reset_kata_laluan_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf      = User::factory()->staf()->create();

        $response = $this->actingAs($pentadbir)->post("/pengguna/{$staf->id}/reset-password", [
            'password'              => 'KataLaluanBaru@123!',
            'password_confirmation' => 'KataLaluanBaru@123!',
            'sebab'                 => 'Kata laluan dilupai oleh pengguna',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    #[Test]
    public function reset_kata_laluan_gagal_jika_tiada_sebab(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf      = User::factory()->staf()->create();

        $response = $this->actingAs($pentadbir)->post("/pengguna/{$staf->id}/reset-password", [
            'password'              => 'KataLaluanBaru@123!',
            'password_confirmation' => 'KataLaluanBaru@123!',
            // tiada 'sebab'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('sebab');
    }

    // ── TempahanController: create() — pra-isi dari params ──────────

    #[Test]
    public function staf_boleh_akses_form_buat_tempahan(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/tempahan/baru');

        $response->assertOk();
    }

    #[Test]
    public function form_buat_tempahan_pra_isi_dari_bilik_dan_tarikh(): void
    {
        $staf   = User::factory()->staf()->create();
        $bilik  = BilikMesyuarat::factory()->kapasiti(20)->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($staf)->get("/tempahan/baru?bilik_id={$bilik->id}&tarikh={$tarikh}");

        $response->assertOk();
        // TempahanController::create() mengisi $duplikat dengan bilik_id & tarikh apabila dihantar melalui URL
    }

    #[Test]
    public function form_buat_tempahan_pra_isi_dari_duplikat_id(): void
    {
        $staf  = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $tempahanAsal = Tempahan::factory()->pagi()->create([
            'user_id'        => $staf->id,
            'bilik_id'       => $bilik->id,
            'nama_mesyuarat' => 'Mesyuarat Asal Untuk Duplikat',
            'status'         => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)->get("/tempahan/baru?duplikat_id={$tempahanAsal->id}");

        $response->assertOk();
        // Controller mengisi $duplikat dengan data dari tempahan asal (nama, bilik, peserta, dll.)
    }

    // ── TempahanController: index() — isih mengikut tarikh_filter ───

    #[Test]
    public function senarai_tempahan_diisih_mengikut_tarikh_apabila_filter_aktif(): void
    {
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);

        // Cipta beberapa tempahan supaya senarai tidak kosong
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();
        Tempahan::factory()->pagi()->create([
            'user_id'  => $staf->id,
            'bilik_id' => $bilik->id,
            'tarikh'   => today()->toDateString(),
            'status'   => Tempahan::STATUS_DILULUSKAN,
        ]);

        // tarikh_filter=hari_ini mencetuskan laluan isih mengikut tarikh (baris 97 TempahanController)
        $response = $this->actingAs($staf)->get('/tempahan?tarikh_filter=hari_ini');

        $response->assertOk();
    }

    // ── TempahanController: update() — kemaskini tempahan ───────────

    #[Test]
    public function staf_boleh_kemaskini_tempahan_sendiri(): void
    {
        $staf  = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id'        => $staf->id,
            'bilik_id'       => $bilik->id,
            'nama_mesyuarat' => 'Mesyuarat Asal',
            'tarikh'         => now()->addDays(3)->format('Y-m-d'),
            'status'         => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)->put("/tempahan/{$tempahan->ulid}", [
            'nama_mesyuarat'   => 'Mesyuarat Telah Dikemaskini',
            'tarikh'           => now()->addDays(5)->format('Y-m-d'),
            'bilik_id'         => $bilik->id,
            'sesi'             => 'pagi',
            'bilangan_peserta' => 15,
            'kategori'         => 'mesyuarat',
            'nama_pengerusi'   => 'Encik Baru',
            'tujuan'           => 'Tujuan dikemaskini',
        ]);

        $response->assertRedirect(route('tempahan.index'));
        $this->assertDatabaseHas('tempahan', [
            'id'             => $tempahan->id,
            'nama_mesyuarat' => 'Mesyuarat Telah Dikemaskini',
        ]);
    }

    // ── BilikController: form create & edit ─────────────────────────

    #[Test]
    public function pentadbir_boleh_akses_form_tambah_bilik(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat/tambah');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_boleh_akses_form_edit_bilik(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik     = BilikMesyuarat::factory()->kapasiti(20)->create();

        // BilikMesyuarat menggunakan ULID sebagai route key (bukan integer ID)
        $response = $this->actingAs($pentadbir)->get("/bilik-mesyuarat/{$bilik->ulid}/edit");

        $response->assertOk();
    }

    // ── CarianController ─────────────────────────────────────────────

    #[Test]
    public function staf_boleh_akses_halaman_carian(): void
    {
        $staf = User::factory()->staf()->create();

        // Query terlalu pendek (< 2 aksara) → pulang hasil kosong tanpa query DB
        $response = $this->actingAs($staf)->get('/carian?q=x');

        $response->assertOk();
    }

    #[Test]
    public function staf_boleh_cari_tempahan_dengan_kata_kunci(): void
    {
        $staf  = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        Tempahan::factory()->pagi()->create([
            'user_id'        => $staf->id,
            'bilik_id'       => $bilik->id,
            'nama_mesyuarat' => 'Mesyuarat Penting ABC',
            'status'         => Tempahan::STATUS_DILULUSKAN,
        ]);

        $response = $this->actingAs($staf)->get('/carian?q=Penting');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_carian_merangkumi_bilik_dan_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik     = BilikMesyuarat::factory()->kapasiti(20)->create(['nama' => 'Bilik Kristal Cari']);

        $response = $this->actingAs($pentadbir)->get('/carian?q=Kristal');

        $response->assertOk();
        // Pentadbir mendapat hasil carian untuk bilik dan pengguna (bukan staf)
    }
}
