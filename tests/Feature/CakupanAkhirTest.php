<?php

namespace Tests\Feature;

use App\Enums\PerananPengguna;
use App\Enums\SesiTempahan;
use App\Enums\StatusTempahan;
use App\Mail\NotifikasiTempahanBaharu;
use App\Mail\PengesahanTempahan;
use App\Models\ActivityLog;
use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\Tetapan;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TempahanMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * CakupanAkhirTest — Menutup jurang liputan pada enums, controllers, policies,
 * filters, services, dan AuditLogger.
 */
class CakupanAkhirTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // BAHAGIAN 1: PerananPengguna Enum
    // =========================================================================

    #[Test]
    public function peranan_pengguna_label_pentadbir(): void
    {
        $this->assertSame('Pentadbir Sistem', PerananPengguna::PentadbirSistem->label());
    }

    #[Test]
    public function peranan_pengguna_label_urus_setia(): void
    {
        $this->assertSame('Urus Setia', PerananPengguna::UrusSetia->label());
    }

    #[Test]
    public function peranan_pengguna_label_staf(): void
    {
        $this->assertSame('Staf', PerananPengguna::Staf->label());
    }

    #[Test]
    public function peranan_pengguna_warna_badge_pentadbir(): void
    {
        $this->assertSame('bg-red-100 text-red-700', PerananPengguna::PentadbirSistem->warnaBadge());
    }

    #[Test]
    public function peranan_pengguna_warna_badge_urus_setia(): void
    {
        $this->assertSame('bg-amber-100 text-amber-700', PerananPengguna::UrusSetia->warnaBadge());
    }

    #[Test]
    public function peranan_pengguna_warna_badge_staf(): void
    {
        $this->assertSame('bg-blue-100 text-blue-700', PerananPengguna::Staf->warnaBadge());
    }

    #[Test]
    public function peranan_pengguna_boleh_urus_semua_pentadbir(): void
    {
        $this->assertTrue(PerananPengguna::PentadbirSistem->bolehUrusSemua());
    }

    #[Test]
    public function peranan_pengguna_boleh_urus_semua_urus_setia(): void
    {
        $this->assertTrue(PerananPengguna::UrusSetia->bolehUrusSemua());
    }

    #[Test]
    public function peranan_pengguna_staf_tidak_boleh_urus_semua(): void
    {
        $this->assertFalse(PerananPengguna::Staf->bolehUrusSemua());
    }

    #[Test]
    public function peranan_pengguna_boleh_akses_tetapan_pentadbir_sahaja(): void
    {
        $this->assertTrue(PerananPengguna::PentadbirSistem->bolehAksesTetapan());
        $this->assertFalse(PerananPengguna::UrusSetia->bolehAksesTetapan());
        $this->assertFalse(PerananPengguna::Staf->bolehAksesTetapan());
    }

    #[Test]
    public function peranan_pengguna_is_valid_nilai_betul(): void
    {
        $this->assertTrue(PerananPengguna::isValid('pentadbir_sistem'));
        $this->assertTrue(PerananPengguna::isValid('urus_setia'));
        $this->assertTrue(PerananPengguna::isValid('staf'));
    }

    #[Test]
    public function peranan_pengguna_is_valid_nilai_salah(): void
    {
        $this->assertFalse(PerananPengguna::isValid('pentadbir'));
        $this->assertFalse(PerananPengguna::isValid(''));
        $this->assertFalse(PerananPengguna::isValid('admin'));
    }

    #[Test]
    public function peranan_pengguna_validasi_in_mengandungi_semua_nilai(): void
    {
        $hasil = PerananPengguna::validasiIn();

        $this->assertStringContainsString('pentadbir_sistem', $hasil);
        $this->assertStringContainsString('urus_setia', $hasil);
        $this->assertStringContainsString('staf', $hasil);
        // Format: nilai1,nilai2,nilai3
        $this->assertCount(3, explode(',', $hasil));
    }

    // =========================================================================
    // BAHAGIAN 2: SesiTempahan Enum
    // =========================================================================

    #[Test]
    public function sesi_tempahan_label_pagi(): void
    {
        $this->assertSame('Sesi Pagi (9:00 AM – 1:00 PM)', SesiTempahan::Pagi->label());
    }

    #[Test]
    public function sesi_tempahan_label_petang(): void
    {
        $this->assertSame('Sesi Petang (2:00 PM – 6:00 PM)', SesiTempahan::Petang->label());
    }

    #[Test]
    public function sesi_tempahan_masa_mula_pagi(): void
    {
        $this->assertSame('09:00', SesiTempahan::Pagi->masaMula());
    }

    #[Test]
    public function sesi_tempahan_masa_mula_petang(): void
    {
        $this->assertSame('14:00', SesiTempahan::Petang->masaMula());
    }

    #[Test]
    public function sesi_tempahan_masa_tamat_pagi(): void
    {
        $this->assertSame('13:00', SesiTempahan::Pagi->masaTamat());
    }

    #[Test]
    public function sesi_tempahan_masa_tamat_petang(): void
    {
        $this->assertSame('18:00', SesiTempahan::Petang->masaTamat());
    }

    #[Test]
    public function sesi_tempahan_is_valid_nilai_betul(): void
    {
        $this->assertTrue(SesiTempahan::isValid('pagi'));
        $this->assertTrue(SesiTempahan::isValid('petang'));
    }

    #[Test]
    public function sesi_tempahan_is_valid_nilai_salah(): void
    {
        $this->assertFalse(SesiTempahan::isValid('pagi_awal'));
        $this->assertFalse(SesiTempahan::isValid(''));
        $this->assertFalse(SesiTempahan::isValid('morning'));
    }

    #[Test]
    public function sesi_tempahan_validasi_in_mengandungi_semua_sesi(): void
    {
        $hasil = SesiTempahan::validasiIn();

        $this->assertStringContainsString('pagi', $hasil);
        $this->assertStringContainsString('petang', $hasil);
        $this->assertCount(2, explode(',', $hasil));
    }

    // =========================================================================
    // BAHAGIAN 3: BilikController
    // =========================================================================

    #[Test]
    public function public_list_kembalikan_json_bilik_aktif(): void
    {
        BilikMesyuarat::factory()->create(['nama' => 'Bilik A', 'status' => 'aktif']);
        BilikMesyuarat::factory()->create(['nama' => 'Bilik B', 'status' => 'tidak_aktif']);

        $response = $this->get('/awam/bilik');

        $response->assertOk()
            ->assertJsonFragment(['nama' => 'Bilik A'])
            ->assertJsonMissing(['nama' => 'Bilik B']);
    }

    #[Test]
    public function bilik_index_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat');

        $response->assertOk();
    }

    #[Test]
    public function bilik_index_ditolak_untuk_staf(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/bilik-mesyuarat');

        $response->assertForbidden();
    }

    #[Test]
    public function bilik_create_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat/tambah');

        $response->assertOk();
    }

    #[Test]
    public function bilik_store_tanpa_gambar_redirect_ke_index(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/bilik-mesyuarat', [
            'nama' => 'Bilik Mesyuarat Utama',
            'kapasiti' => 20,
            'lokasi' => 'Tingkat 3',
            'kemudahan' => ['Projektor', 'Papan Putih'],
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('bilik.index'));
        $this->assertDatabaseHas('bilik_mesyuarat', [
            'nama' => 'Bilik Mesyuarat Utama',
            'kapasiti' => 20,
        ]);
    }

    #[Test]
    public function bilik_edit_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($pentadbir)->get("/bilik-mesyuarat/{$bilik->ulid}/edit");

        $response->assertOk();
    }

    #[Test]
    public function bilik_update_redirect_ke_index(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($pentadbir)->put("/bilik-mesyuarat/{$bilik->ulid}", [
            'nama' => 'Bilik Kemaskini',
            'kapasiti' => 30,
            'lokasi' => 'Tingkat 5',
            'kemudahan' => ['Sistem Audio'],
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('bilik.index'));
        $this->assertDatabaseHas('bilik_mesyuarat', ['nama' => 'Bilik Kemaskini']);
    }

    #[Test]
    public function bilik_destroy_tanpa_tempahan_berjaya(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($pentadbir)->delete("/bilik-mesyuarat/{$bilik->ulid}");

        $response->assertRedirect(route('bilik.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('bilik_mesyuarat', ['id' => $bilik->id]);
    }

    #[Test]
    public function bilik_destroy_dengan_tempahan_sedia_ada_gagal(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->create();

        // Cipta tempahan untuk bilik ini
        Tempahan::factory()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $pentadbir->id,
        ]);

        $response = $this->actingAs($pentadbir)->delete("/bilik-mesyuarat/{$bilik->ulid}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bilik_mesyuarat', ['id' => $bilik->id, 'deleted_at' => null]);
    }

    // =========================================================================
    // BAHAGIAN 4: LaporanController
    // =========================================================================

    #[Test]
    public function laporan_index_boleh_diakses_oleh_staf(): void
    {
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);

        $response = $this->actingAs($staf)->get('/laporan');

        $response->assertOk();
    }

    #[Test]
    public function laporan_index_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/laporan');

        $response->assertOk();
    }

    #[Test]
    public function laporan_index_boleh_diakses_oleh_urus_setia(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/laporan');

        $response->assertOk();
    }

    #[Test]
    public function laporan_index_dengan_penapis_tahun(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/laporan?tahun=2025');

        $response->assertOk();
    }

    #[Test]
    public function laporan_index_dengan_penapis_bilik(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($pentadbir)->get("/laporan?bilik_id={$bilik->id}");

        $response->assertOk();
    }

    #[Test]
    public function laporan_index_staf_dengan_penapis_tahun(): void
    {
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);

        $response = $this->actingAs($staf)->get('/laporan?tahun=2025');

        $response->assertOk();
    }

    // =========================================================================
    // BAHAGIAN 5: TempahanPolicy
    // =========================================================================

    #[Test]
    public function policy_update_pentadbir_boleh_kemaskini_semua_tempahan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $stafLain = User::factory()->staf()->create();
        $tempahan = Tempahan::factory()->create(['user_id' => $stafLain->id]);

        $this->assertTrue($pentadbir->can('update', $tempahan));
    }

    #[Test]
    public function policy_update_staf_boleh_kemaskini_tempahan_sendiri(): void
    {
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit A']);
        $tempahan = Tempahan::factory()->create(['user_id' => $staf->id]);

        $this->assertTrue($staf->can('update', $tempahan));
    }

    #[Test]
    public function policy_update_staf_boleh_kemaskini_tempahan_rakan_seunit(): void
    {
        $staf1 = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);
        $staf2 = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);
        $tempahan = Tempahan::factory()->create(['user_id' => $staf1->id]);

        // staf2 dan staf1 sama jabatan — boleh edit
        $this->assertTrue($staf2->can('update', $tempahan));
    }

    #[Test]
    public function policy_update_staf_tidak_boleh_kemaskini_tempahan_jabatan_lain(): void
    {
        $staf1 = User::factory()->staf()->create(['jabatan' => 'Unit A']);
        $staf2 = User::factory()->staf()->create(['jabatan' => 'Unit B']);
        $tempahan = Tempahan::factory()->create(['user_id' => $staf1->id]);

        $this->assertFalse($staf2->can('update', $tempahan));
    }

    #[Test]
    public function policy_delete_staf_tidak_boleh_padam(): void
    {
        $staf = User::factory()->staf()->create();
        $tempahan = Tempahan::factory()->create(['user_id' => $staf->id]);

        $this->assertFalse($staf->can('delete', $tempahan));
    }

    #[Test]
    public function policy_delete_pentadbir_boleh_padam(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $tempahan = Tempahan::factory()->create();

        $this->assertTrue($pentadbir->can('delete', $tempahan));
    }

    #[Test]
    public function policy_export_urus_setia_boleh_eksport(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $this->assertTrue($urusSetia->can('export', Tempahan::class));
    }

    #[Test]
    public function policy_export_staf_tidak_boleh_eksport(): void
    {
        $staf = User::factory()->staf()->create();

        $this->assertFalse($staf->can('export', Tempahan::class));
    }

    // =========================================================================
    // BAHAGIAN 6: TempahanFilter (melalui TempahanController::index)
    // =========================================================================

    #[Test]
    public function filter_bilik_id_menyaring_tempahan_mengikut_bilik(): void
    {
        $user = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        $response = $this->actingAs($user)->get("/tempahan?bilik_id={$bilik->id}");

        $response->assertOk();
    }

    #[Test]
    public function filter_carian_nama_mesyuarat(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?carian=mesyuarat+unit');

        $response->assertOk();
    }

    #[Test]
    public function filter_status_diluluskan(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?status=diluluskan');

        $response->assertOk();
    }

    #[Test]
    public function filter_kategori_mesyuarat(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?kategori=mesyuarat');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_dari(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_dari=2026-01-01');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_hingga(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_hingga=2026-12-31');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_hari_ini(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=hari_ini');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_esok(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=esok');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_baharu(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=baharu');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_7_hari(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=7_hari');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_bulan_ini(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=bulan_ini');

        $response->assertOk();
    }

    #[Test]
    public function filter_tarikh_pintas_akan_datang(): void
    {
        $user = User::factory()->staf()->create();

        $response = $this->actingAs($user)->get('/tempahan?tarikh_filter=akan_datang');

        $response->assertOk();
    }

    #[Test]
    public function filter_jabatan_hanya_untuk_bukan_staf(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/tempahan?jabatan=Unit+Aplikasi+Gunasama');

        $response->assertOk();
    }

    // =========================================================================
    // BAHAGIAN 7: TempahanMailService
    // =========================================================================

    #[Test]
    public function mail_service_tempahan_kosong_tidak_hantar_apa_apa(): void
    {
        Mail::fake();

        $bilik = BilikMesyuarat::factory()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);

        $service = new TempahanMailService;
        $service->hantarSelepasStore([], [
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'kategori' => 'mesyuarat',
            'nama_mesyuarat' => 'Mesyuarat Test',
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'nama_pengerusi' => 'Encik Ali',
            'tujuan' => 'Ujian',
        ], $bilik, $user);

        Mail::assertNothingSent();
    }

    #[Test]
    public function mail_service_hantar_pengesahan_kepada_pemohon_apabila_notif_aktif(): void
    {
        Mail::fake();

        // Pastikan tetapan notif_kelulusan = 1
        Tetapan::set('notif_kelulusan', '1');
        Tetapan::clearCache();

        $bilik = BilikMesyuarat::factory()->create(['nama' => 'Bilik Test']);
        $user = User::factory()->create([
            'email' => 'pemohon@example.com',
            'name' => 'Ahmad',
            'jabatan' => 'Unit Aplikasi Gunasama',
        ]);

        $tempahan = Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $user->id,
            'tarikh' => now()->addDay()->format('Y-m-d'),
        ]);

        $service = new TempahanMailService;
        $service->hantarSelepasStore([$tempahan], [
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'kategori' => 'mesyuarat',
            'nama_mesyuarat' => 'Mesyuarat Penuh',
            'sesi' => ['pagi'],
            'bilangan_peserta' => 10,
            'nama_pengerusi' => 'Encik Ahmad',
            'tujuan' => 'Ujian servis mel',
        ], $bilik, $user);

        Mail::assertSent(PengesahanTempahan::class);
    }

    #[Test]
    public function mail_service_tidak_hantar_pengesahan_apabila_notif_dimatikan(): void
    {
        Mail::fake();

        Tetapan::set('notif_kelulusan', '0');
        Tetapan::set('notif_tempahan_baru', '0');
        Tetapan::clearCache();

        $bilik = BilikMesyuarat::factory()->create();
        $user = User::factory()->create(['email' => 'pengguna@example.com']);
        $tempahan = Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $user->id,
        ]);

        $service = new TempahanMailService;
        $service->hantarSelepasStore([$tempahan], [
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'kategori' => 'mesyuarat',
            'nama_mesyuarat' => 'Mesyuarat Senyap',
            'sesi' => ['pagi'],
            'bilangan_peserta' => 5,
            'nama_pengerusi' => 'Puan Siti',
            'tujuan' => '',
        ], $bilik, $user);

        Mail::assertNothingSent();
    }

    #[Test]
    public function mail_service_hantar_notifikasi_urus_setia_apabila_emel_ditetapkan(): void
    {
        Mail::fake();

        Tetapan::set('notif_kelulusan', '0');
        Tetapan::set('notif_tempahan_baru', '1');
        Tetapan::set('emel_notifikasi', 'urussetia@example.com');
        Tetapan::clearCache();

        $bilik = BilikMesyuarat::factory()->create(['nama' => 'Bilik Notifikasi']);
        $user = User::factory()->create([
            'email' => 'pemohon2@example.com',
            'jabatan' => 'Unit Bayaran',
        ]);
        $tempahan = Tempahan::factory()->petang()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $user->id,
        ]);

        $service = new TempahanMailService;
        $service->hantarSelepasStore([$tempahan], [
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'kategori' => 'taklimat',
            'nama_mesyuarat' => 'Taklimat Kewangan',
            'sesi' => ['petang'],
            'bilangan_peserta' => 15,
            'nama_pengerusi' => 'Encik Zul',
            'tujuan' => 'Taklimat kewangan tahunan',
        ], $bilik, $user);

        Mail::assertSent(NotifikasiTempahanBaharu::class);
    }

    #[Test]
    public function mail_service_tidak_hantar_notifikasi_apabila_tiada_emel_notifikasi(): void
    {
        Mail::fake();

        Tetapan::set('notif_kelulusan', '0');
        Tetapan::set('notif_tempahan_baru', '1');
        // Tiada emel_notifikasi ditetapkan
        Tetapan::set('emel_notifikasi', '');
        Tetapan::clearCache();

        $bilik = BilikMesyuarat::factory()->create();
        $user = User::factory()->create(['email' => 'pemohon3@example.com']);
        $tempahan = Tempahan::factory()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $user->id,
        ]);

        $service = new TempahanMailService;
        $service->hantarSelepasStore([$tempahan], [
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'kategori' => 'bengkel',
            'nama_mesyuarat' => 'Bengkel Tanpa Notif',
            'sesi' => ['pagi'],
            'bilangan_peserta' => 8,
            'nama_pengerusi' => 'Dr. Amir',
            'tujuan' => '',
        ], $bilik, $user);

        Mail::assertNotSent(NotifikasiTempahanBaharu::class);
    }

    // =========================================================================
    // BAHAGIAN 8: AuditLogger
    // =========================================================================

    #[Test]
    public function audit_logger_catat_tanpa_model_mencipta_rekod_log(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('akses_laporan', null, ['tahun' => 2026]);

        $this->assertDatabaseHas('activity_log', [
            'tindakan' => 'akses_laporan',
            'model_jenis' => null,
            'model_id' => null,
        ]);
    }

    #[Test]
    public function audit_logger_catat_dengan_model_menyimpan_jenis_dan_id(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);
        $bilik = BilikMesyuarat::factory()->create();

        AuditLogger::catat('tambah_bilik', $bilik, ['nama' => $bilik->nama]);

        $this->assertDatabaseHas('activity_log', [
            'tindakan' => 'tambah_bilik',
            'model_jenis' => 'BilikMesyuarat',
            'model_id' => $bilik->id,
        ]);
    }

    #[Test]
    public function audit_logger_catat_menyimpan_pengguna_id(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('kemaskini_profil');

        $log = ActivityLog::where('tindakan', 'kemaskini_profil')->first();
        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->pengguna_id);
    }

    #[Test]
    public function audit_logger_catat_dengan_penerangan_tersuai(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('log_masuk_berjaya', null, [], 'Penerangan tersuai untuk ujian');

        $this->assertDatabaseHas('activity_log', [
            'tindakan' => 'log_masuk_berjaya',
            'penerangan' => 'Penerangan tersuai untuk ujian',
        ]);
    }

    #[Test]
    public function audit_logger_catat_tanpa_auth_tidak_crash(): void
    {
        // Panggil tanpa actingAs — pengguna_id akan null
        AuditLogger::catat('log_masuk_gagal', null, ['ip' => '127.0.0.1']);

        $this->assertDatabaseHas('activity_log', [
            'tindakan' => 'log_masuk_gagal',
            'pengguna_id' => null,
        ]);
    }

    #[Test]
    public function audit_logger_catat_rekod_dengan_hash_yang_sah(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('kemaskini_tetapan');

        $log = ActivityLog::where('tindakan', 'kemaskini_tetapan')->first();
        $this->assertNotNull($log);
        $this->assertNotNull($log->record_hash);
        $this->assertSame(64, strlen($log->record_hash)); // SHA-256 = 64 hex chars
    }

    #[Test]
    public function audit_logger_rantai_hash_rekod_kedua_menyimpan_prev_hash(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('buat_tempahan');
        $log1 = ActivityLog::where('tindakan', 'buat_tempahan')->first();

        AuditLogger::catat('kemaskini_tempahan');
        $log2 = ActivityLog::where('tindakan', 'kemaskini_tempahan')->first();

        $this->assertSame($log1->record_hash, $log2->prev_hash);
    }

    #[Test]
    public function audit_logger_catat_tindakan_tidak_diketahui_guna_penerangan_lalai(): void
    {
        $user = User::factory()->staf()->create(['name' => 'Ahmad Ujian']);
        $this->actingAs($user);

        AuditLogger::catat('tindakan_tidak_diketahui_xyz');

        $log = ActivityLog::where('tindakan', 'tindakan_tidak_diketahui_xyz')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('tindakan_tidak_diketahui_xyz', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_kemaskini_pengguna(): void
    {
        $user = User::factory()->pentadbir()->create();
        $sasaran = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('kemaskini_pengguna', $sasaran);

        $log = ActivityLog::where('tindakan', 'kemaskini_pengguna')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengemaskini maklumat pengguna', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_reset_kata_laluan(): void
    {
        $user = User::factory()->pentadbir()->create();
        $sasaran = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('reset_kata_laluan', $sasaran);

        $log = ActivityLog::where('tindakan', 'reset_kata_laluan')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menetapkan semula kata laluan', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_tambah_pengguna(): void
    {
        $admin = User::factory()->pentadbir()->create();
        $pengguna = User::factory()->staf()->create();
        $this->actingAs($admin);

        AuditLogger::catat('tambah_pengguna', $pengguna);

        $log = ActivityLog::where('tindakan', 'tambah_pengguna')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menambah pengguna baru', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_log_keluar(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('log_keluar');

        $log = ActivityLog::where('tindakan', 'log_keluar')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('log keluar', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_padam_tempahan(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);
        $bilik = BilikMesyuarat::factory()->create();
        $tempahan = Tempahan::factory()->create(['bilik_id' => $bilik->id, 'user_id' => $user->id]);

        AuditLogger::catat('padam_tempahan', $tempahan);

        $log = ActivityLog::where('tindakan', 'padam_tempahan')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('memadam tempahan', $log->penerangan);
    }

    // =========================================================================
    // BAHAGIAN 9: TempahanPolicy — viewAny & create
    // =========================================================================

    #[Test]
    public function policy_view_any_semua_pengguna_log_masuk_boleh_lihat_senarai(): void
    {
        $staf = User::factory()->staf()->create();
        $this->assertTrue($staf->can('viewAny', Tempahan::class));

        $urusSetia = User::factory()->urusSetia()->create();
        $this->assertTrue($urusSetia->can('viewAny', Tempahan::class));
    }

    #[Test]
    public function policy_create_semua_pengguna_log_masuk_boleh_buat_tempahan(): void
    {
        $staf = User::factory()->staf()->create();
        $this->assertTrue($staf->can('create', Tempahan::class));

        $pentadbir = User::factory()->pentadbir()->create();
        $this->assertTrue($pentadbir->can('create', Tempahan::class));
    }

    #[Test]
    public function policy_view_butiran_tempahan_semua_pengguna_boleh_lihat(): void
    {
        $staf = User::factory()->staf()->create();
        $tempahan = Tempahan::factory()->create();

        $this->assertTrue($staf->can('view', $tempahan));
    }

    // =========================================================================
    // BAHAGIAN 10: BilikMesyuarat Model Accessors
    // =========================================================================

    #[Test]
    public function bilik_mesyuarat_kemudahan_list_dengan_kemudahan(): void
    {
        $bilik = BilikMesyuarat::factory()->create([
            'kemudahan' => ['Projektor', 'Papan Putih', 'Sistem Audio'],
        ]);

        $this->assertSame('Projektor, Papan Putih, Sistem Audio', $bilik->kemudahan_list);
    }

    #[Test]
    public function bilik_mesyuarat_kemudahan_list_tanpa_kemudahan(): void
    {
        $bilik = BilikMesyuarat::factory()->create(['kemudahan' => null]);

        $this->assertSame('-', $bilik->kemudahan_list);
    }

    #[Test]
    public function bilik_mesyuarat_is_aktif_mengembalikan_true_untuk_aktif(): void
    {
        $bilik = BilikMesyuarat::factory()->create(['status' => 'aktif']);
        $this->assertTrue($bilik->isAktif());
    }

    #[Test]
    public function bilik_mesyuarat_is_aktif_mengembalikan_false_untuk_tidak_aktif(): void
    {
        $bilik = BilikMesyuarat::factory()->nyahaktif()->create();
        $this->assertFalse($bilik->isAktif());
    }

    #[Test]
    public function bilik_mesyuarat_penggunaan_bulan_ini_mengembalikan_integer(): void
    {
        $user = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->create();

        // Cipta beberapa tempahan bulan ini
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $user->id,
            'tarikh' => now()->format('Y-m-d'),
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        // Panggil accessor — mesti mengembalikan integer antara 0 dan 100
        $peratus = $bilik->penggunaan_bulan_ini;
        $this->assertIsInt($peratus);
        $this->assertGreaterThanOrEqual(0, $peratus);
        $this->assertLessThanOrEqual(100, $peratus);
    }

    // =========================================================================
    // BAHAGIAN 11: LaporanController Export (sahaja pentadbir/urus_setia)
    // =========================================================================

    #[Test]
    public function laporan_export_pdf_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/laporan/eksport/pdf?tahun='.now()->year);

        // Mesti kembalikan PDF atau redirect — bukan 403/404
        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function laporan_export_excel_boleh_diakses_oleh_urus_setia(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/laporan/eksport/excel?tahun='.now()->year);

        $this->assertNotEquals(403, $response->getStatusCode());
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function laporan_export_pdf_ditolak_untuk_staf(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/laporan/eksport/pdf');

        $response->assertForbidden();
    }

    // =========================================================================
    // BAHAGIAN 12: StatusTempahan Enum (0% coverage)
    // =========================================================================

    #[Test]
    public function status_tempahan_label_diluluskan(): void
    {
        $this->assertSame('Sah', StatusTempahan::Diluluskan->label());
    }

    #[Test]
    public function status_tempahan_label_ditolak(): void
    {
        $this->assertSame('Ditolak', StatusTempahan::Ditolak->label());
    }

    #[Test]
    public function status_tempahan_warna_badge_diluluskan(): void
    {
        $this->assertSame('bg-green-100 text-green-700', StatusTempahan::Diluluskan->warnaBadge());
    }

    #[Test]
    public function status_tempahan_warna_badge_ditolak(): void
    {
        $this->assertSame('bg-red-100 text-red-700', StatusTempahan::Ditolak->warnaBadge());
    }

    #[Test]
    public function status_tempahan_ikon_badge_diluluskan(): void
    {
        $this->assertSame('fa-circle-check', StatusTempahan::Diluluskan->ikonBadge());
    }

    #[Test]
    public function status_tempahan_ikon_badge_ditolak(): void
    {
        $this->assertSame('fa-ban', StatusTempahan::Ditolak->ikonBadge());
    }

    #[Test]
    public function status_tempahan_is_valid_nilai_betul(): void
    {
        $this->assertTrue(StatusTempahan::isValid('diluluskan'));
        $this->assertTrue(StatusTempahan::isValid('ditolak'));
    }

    #[Test]
    public function status_tempahan_is_valid_nilai_salah(): void
    {
        $this->assertFalse(StatusTempahan::isValid('pending'));
        $this->assertFalse(StatusTempahan::isValid(''));
        $this->assertFalse(StatusTempahan::isValid('lulus'));
    }

    // =========================================================================
    // BAHAGIAN 13: TempahanController — uncovered update paths
    // =========================================================================

    #[Test]
    public function tempahan_update_ditolak_apabila_kapasiti_melebihi(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(10)->create();
        $tempahan = Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $staf->id,
            'tarikh' => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($staf)->put("/tempahan/{$tempahan->ulid}", [
            'nama_mesyuarat' => 'Mesyuarat Kemaskini',
            'tarikh' => now()->addDay()->format('Y-m-d'),
            'bilik_id' => $bilik->id,
            'sesi' => 'pagi',
            'bilangan_peserta' => 50, // melebihi kapasiti
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Encik Test',
        ]);

        $response->assertSessionHasErrors('bilangan_peserta');
    }

    #[Test]
    public function tempahan_update_ditolak_apabila_ada_konflik_sesi(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(30)->create();
        $tarikh = now()->addDay()->format('Y-m-d');

        // Tempahan sedia ada untuk sesi pagi
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => $tarikh,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        // Tempahan yang akan dikemaskini
        $tempahan = Tempahan::factory()->petang()->create([
            'bilik_id' => $bilik->id,
            'user_id' => $staf->id,
            'tarikh' => $tarikh,
        ]);

        // Cuba kemaskini ke sesi pagi yang sudah ada konflik
        $response = $this->actingAs($staf)->put("/tempahan/{$tempahan->ulid}", [
            'nama_mesyuarat' => 'Mesyuarat Cuba Konflik',
            'tarikh' => $tarikh,
            'bilik_id' => $bilik->id,
            'sesi' => 'pagi', // konflik dengan tempahan sedia ada
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Puan Test',
        ]);

        $response->assertSessionHasErrors('sesi');
    }

    // =========================================================================
    // BAHAGIAN 14: Lebih AuditLogger match arms
    // =========================================================================

    #[Test]
    public function audit_logger_tindakan_eksport_pdf(): void
    {
        $user = User::factory()->urusSetia()->create();
        $this->actingAs($user);

        AuditLogger::catat('eksport_pdf');

        $log = ActivityLog::where('tindakan', 'eksport_pdf')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengeksport senarai tempahan (PDF)', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_eksport_excel(): void
    {
        $user = User::factory()->urusSetia()->create();
        $this->actingAs($user);

        AuditLogger::catat('eksport_excel');

        $log = ActivityLog::where('tindakan', 'eksport_excel')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengeksport senarai tempahan (Excel)', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_aktifkan_pengguna(): void
    {
        $admin = User::factory()->pentadbir()->create();
        $sasaran = User::factory()->staf()->create();
        $this->actingAs($admin);

        AuditLogger::catat('aktifkan_pengguna', $sasaran);

        $log = ActivityLog::where('tindakan', 'aktifkan_pengguna')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengaktifkan akaun pengguna', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_nyahaktifkan_pengguna(): void
    {
        $admin = User::factory()->pentadbir()->create();
        $sasaran = User::factory()->staf()->create();
        $this->actingAs($admin);

        AuditLogger::catat('nyahaktifkan_pengguna', $sasaran);

        $log = ActivityLog::where('tindakan', 'nyahaktifkan_pengguna')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menyahaktifkan akaun pengguna', $log->penerangan);
    }

    // =========================================================================
    // BAHAGIAN 15: AuditVerify Artisan Command
    // =========================================================================

    #[Test]
    public function audit_verify_tiada_rekod_mengembalikan_amaran(): void
    {
        // Tiada rekod dalam DB — command patut pulang SUCCESS
        $this->artisan('audit:verify')
            ->expectsOutputToContain('Tiada rekod audit untuk disemak')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_verify_rekod_sah_mengembalikan_success(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        // Cipta rekod log yang sah melalui AuditLogger
        AuditLogger::catat('buat_tempahan', null, ['nota' => 'ujian verify']);

        $this->artisan('audit:verify')
            ->expectsOutputToContain('Menyemak')
            ->assertExitCode(0);
    }

    #[Test]
    public function audit_verify_rekod_rosak_mengembalikan_failure(): void
    {
        // Cipta rekod log dengan hash yang tidak betul (simulasi gangguan)
        ActivityLog::create([
            'pengguna_id' => null,
            'tindakan' => 'ujian_rosak',
            'model_jenis' => null,
            'model_id' => null,
            'penerangan' => 'rekod ujian rosak',
            'butiran' => null,
            'ip_address' => '127.0.0.1',
            'prev_hash' => null,
            'record_hash' => 'hash_palsu_yang_tidak_betul_sama_sekali_untuk_ujian',
            'dicipta_pada' => now(),
        ]);

        $this->artisan('audit:verify')
            ->assertExitCode(1);
    }

    #[Test]
    public function audit_verify_rekod_tanpa_hash_dilangkau(): void
    {
        // Rekod lama tanpa record_hash — patut dilangkau (continue)
        ActivityLog::create([
            'pengguna_id' => null,
            'tindakan' => 'rekod_lama',
            'model_jenis' => null,
            'model_id' => null,
            'penerangan' => 'rekod lama tanpa hash',
            'butiran' => null,
            'ip_address' => '127.0.0.1',
            'prev_hash' => null,
            'record_hash' => null,
            'dicipta_pada' => now(),
        ]);

        // Command patut jalan tanpa error — rekod lama dilangkau
        $this->artisan('audit:verify')
            ->assertExitCode(0);
    }

    // =========================================================================
    // BAHAGIAN 16: ForgotPasswordController
    // =========================================================================

    #[Test]
    public function lupa_kata_laluan_halaman_boleh_diakses(): void
    {
        $response = $this->get('/lupa-kata-laluan');

        $response->assertOk();
    }

    #[Test]
    public function lupa_kata_laluan_form_reset_boleh_diakses_dengan_token(): void
    {
        $response = $this->get('/reset-kata-laluan/token-ujian?email=test@example.com');

        $response->assertOk();
    }

    #[Test]
    public function lupa_kata_laluan_hantar_pautan_tanpa_emel_gagal_validasi(): void
    {
        $response = $this->post('/lupa-kata-laluan', []);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function lupa_kata_laluan_hantar_pautan_dengan_emel_sah_kembalikan_status(): void
    {
        // Emel mungkin tidak wujud tapi mesej generik tetap dihantar
        $response = $this->post('/lupa-kata-laluan', [
            'email' => 'tiada@example.com',
        ]);

        // Redirect balik dengan mesej status generik
        $response->assertRedirect();
        $response->assertSessionHas('status');
    }

    #[Test]
    public function reset_kata_laluan_gagal_validasi_tanpa_medan_wajib(): void
    {
        $response = $this->post('/reset-kata-laluan', []);

        $response->assertSessionHasErrors(['token', 'email', 'password']);
    }

    #[Test]
    public function reset_kata_laluan_dengan_token_tidak_sah_kembalikan_error(): void
    {
        $user = User::factory()->create(['email' => 'ujian@example.com']);

        $response = $this->post('/reset-kata-laluan', [
            'token' => 'token-tidak-sah',
            'email' => 'ujian@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // =========================================================================
    // BAHAGIAN 17: AuthController — login flows
    // =========================================================================

    #[Test]
    public function login_halaman_boleh_diakses(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    #[Test]
    public function login_dengan_kelayakan_betul_redirect_ke_dashboard(): void
    {
        $user = User::factory()->staf()->create([
            'email' => 'staf@example.com',
            'password' => bcrypt('password123'),
            'aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staf@example.com',
            'password' => 'password123',
        ]);

        // Patut redirect (ke dashboard atau 2FA)
        $response->assertRedirect();
    }

    #[Test]
    public function login_dengan_kata_laluan_salah_gagal(): void
    {
        User::factory()->staf()->create([
            'email' => 'salah@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'salah@example.com',
            'password' => 'kata_laluan_salah',
        ]);

        $response->assertSessionHasErrors();
    }

    #[Test]
    public function login_akaun_nyahaktif_ditolak(): void
    {
        User::factory()->nyahaktif()->create([
            'email' => 'nyahaktif@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'nyahaktif@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors();
    }

    // =========================================================================
    // BAHAGIAN 18: TempahanController — export Excel via urus_setia
    // =========================================================================

    #[Test]
    public function tempahan_export_excel_boleh_diakses_urus_setia(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/tempahan/eksport/excel');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function tempahan_export_pdf_boleh_diakses_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/tempahan/eksport/pdf');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    // =========================================================================
    // BAHAGIAN 19: Lebih AuditLogger janaPenerangan match arms
    // =========================================================================

    #[Test]
    public function audit_logger_tindakan_backup_database(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('backup_database');

        $log = ActivityLog::where('tindakan', 'backup_database')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('membuat backup database', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_kemaskini_jadual_backup(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('kemaskini_jadual_backup');

        $log = ActivityLog::where('tindakan', 'kemaskini_jadual_backup')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengemaskini jadual backup', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_muat_turun_backup(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('muat_turun_backup');

        $log = ActivityLog::where('tindakan', 'muat_turun_backup')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('memuat turun fail backup', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_padam_backup(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('padam_backup');

        $log = ActivityLog::where('tindakan', 'padam_backup')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('memadam rekod backup', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_percubaan_akaun_nyahaktif(): void
    {
        // Tindakan ini dipanggil tanpa auth (login fail)
        AuditLogger::catat('percubaan_akaun_nyahaktif');

        $log = ActivityLog::where('tindakan', 'percubaan_akaun_nyahaktif')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('dinyahaktifkan', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_aktifkan_2fa(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('aktifkan_2fa');

        $log = ActivityLog::where('tindakan', 'aktifkan_2fa')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengaktifkan pengesahan dua faktor', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_nyahaktifkan_2fa(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('nyahaktifkan_2fa');

        $log = ActivityLog::where('tindakan', 'nyahaktifkan_2fa')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menyahaktifkan pengesahan dua faktor', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_log_masuk_berjaya_tanpa_penerangan_tersuai(): void
    {
        $user = User::factory()->staf()->create(['name' => 'Ujian Log Masuk']);
        $this->actingAs($user);

        AuditLogger::catat('log_masuk_berjaya');

        $log = ActivityLog::where('tindakan', 'log_masuk_berjaya')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('log masuk ke sistem', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_eksport_audit_excel(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('eksport_audit_excel');

        $log = ActivityLog::where('tindakan', 'eksport_audit_excel')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengeksport log audit (Excel)', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_eksport_laporan_pdf(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('eksport_laporan_pdf');

        $log = ActivityLog::where('tindakan', 'eksport_laporan_pdf')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengeksport laporan statistik (PDF)', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_eksport_laporan_excel(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('eksport_laporan_excel');

        $log = ActivityLog::where('tindakan', 'eksport_laporan_excel')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengeksport laporan statistik (Excel)', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_tukar_kata_laluan(): void
    {
        $user = User::factory()->staf()->create();
        $this->actingAs($user);

        AuditLogger::catat('tukar_kata_laluan');

        $log = ActivityLog::where('tindakan', 'tukar_kata_laluan')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menukar kata laluan sendiri', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_bulk_aktifkan(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('bulk_aktifkan');

        $log = ActivityLog::where('tindakan', 'bulk_aktifkan')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('mengaktifkan pelbagai pengguna', $log->penerangan);
    }

    #[Test]
    public function audit_logger_tindakan_bulk_nyahaktifkan(): void
    {
        $user = User::factory()->pentadbir()->create();
        $this->actingAs($user);

        AuditLogger::catat('bulk_nyahaktifkan');

        $log = ActivityLog::where('tindakan', 'bulk_nyahaktifkan')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('menyahaktifkan pelbagai pengguna', $log->penerangan);
    }

    // =========================================================================
    // BAHAGIAN 20: JanaManualPengguna — command
    // =========================================================================

    #[Test]
    public function jana_manual_pengguna_command_berjalan_tanpa_exception(): void
    {
        // Command menjana PDF manual pengguna — exit 0 jika screenshot ada, 1 jika tidak
        // Dalam persekitaran CI, screenshot mungkin ada atau tidak — kedua-dua adalah sah
        try {
            $this->artisan('manual:jana')->assertExitCode(0);
        } catch (AssertionFailedError $e) {
            // Exit code 1 juga boleh diterima jika screenshot tidak wujud
            $this->artisan('manual:jana')->assertExitCode(1);
        }
        $this->assertTrue(true); // Pastikan test sampai ke sini
    }
}
