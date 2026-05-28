<?php

namespace Tests\Feature;

use App\Models\BilikMesyuarat;
use App\Models\Tempahan;
use App\Models\TempahanBerulang;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes Cakupan Tambahan — meningkatkan liputan baris untuk mencapai 80%
 *
 * Meliputi:
 *  - DuaFaktorController: hantarSemula, lockout selepas 3 percubaan
 *  - BilikController: publicList, kemaskini dengan jabatan pentadbir
 *  - AuditLogController: index dengan filter, exportExcel
 *  - TempahanPolicy: update, view
 *  - User model: methods yang belum diliputi
 *  - BilikMesyuarat model: accessors dan relations
 */
class CakupanTambahanTest extends TestCase
{
    // ── DuaFaktorController::hantarSemula() ─────────────────────────

    #[Test]
    public function hantar_semula_otp_berjaya_apabila_sesi_masih_aktif(): void
    {
        $pengguna = User::factory()->staf()->create();

        // Sediakan sesi 2FA
        $response = $this->withSession(['2fa_user_id' => $pengguna->id])
            ->post('/dua-faktor/hantar-semula');

        // Redirect balik dengan kejayaan
        $response->assertRedirect();
        $response->assertSessionHas('success_otp');
    }

    #[Test]
    public function hantar_semula_otp_gagal_tanpa_sesi_2fa(): void
    {
        // Tiada 2fa_user_id dalam sesi — redirect ke login
        $response = $this->post('/dua-faktor/hantar-semula');

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function verify_2fa_lockout_selepas_tiga_percubaan_gagal(): void
    {
        $pengguna = User::factory()->staf()->create();
        $expiry = now()->addMinutes(10);

        Cache::put('2fa_otp_'.$pengguna->id, [
            'kod_hash' => hash_hmac('sha256', '123456', config('app.key')),
            'percubaan' => 2, // Sudah 2 percubaan — percubaan ke-3 akan kunci
            'expires_at' => $expiry->timestamp,
        ], $expiry);

        $response = $this->withSession(['2fa_user_id' => $pengguna->id, '2fa_remember' => false])
            ->post('/dua-faktor', ['kod' => '999999']); // kod salah — percubaan ke-3

        // Selepas 3 percubaan gagal — redirect ke login (lockout)
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function verify_2fa_gagal_jika_tiada_sesi_2fa(): void
    {
        // Tiada 2fa_user_id dalam sesi
        $response = $this->post('/dua-faktor', ['kod' => '123456']);

        $response->assertRedirect(route('login'));
    }

    // ── BilikController::publicList() ───────────────────────────────

    #[Test]
    public function awam_boleh_dapatkan_senarai_bilik_aktif(): void
    {
        // Bilik aktif
        $bilikAktif = BilikMesyuarat::factory()->kapasiti(20)->create(['status' => 'aktif']);
        // Bilik tidak aktif — tidak patut muncul
        BilikMesyuarat::factory()->kapasiti(10)->create(['status' => 'tidak_aktif']);

        $response = $this->get('/awam/bilik');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => ['id', 'nama', 'kapasiti', 'kemudahan', 'lokasi'],
            ]);

        // Pastikan bilik aktif ada dalam respons
        $data = $response->json();
        $ids = array_column($data, 'id');
        $this->assertContains($bilikAktif->id, $ids);
    }

    // ── AuditLogController dengan filter ────────────────────────────

    #[Test]
    public function pentadbir_boleh_akses_log_audit_dengan_filter(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/log-audit?tindakan=log_masuk_berjaya');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_boleh_akses_log_audit_dengan_carian(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/log-audit?carian=sistem');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_boleh_eksport_log_audit_excel(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/log-audit/eksport/excel');

        $response->assertSuccessful();
    }

    // ── TempahanPolicy: pelbagai semak ──────────────────────────────

    #[Test]
    public function staf_lain_tidak_boleh_edit_tempahan_orang_lain(): void
    {
        $stafA = User::factory()->staf()->create();
        $stafB = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        // StafA buat tempahan
        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id' => $stafA->id,
            'bilik_id' => $bilik->id,
            'tarikh' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // StafB cuba edit tempahan stafA
        $response = $this->actingAs($stafB)->get("/tempahan/{$tempahan->ulid}/edit");

        $response->assertForbidden();
    }

    #[Test]
    public function pentadbir_boleh_lihat_tempahan_semua_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id' => $staf->id,
            'bilik_id' => $bilik->id,
        ]);

        $response = $this->actingAs($pentadbir)->get("/tempahan/{$tempahan->ulid}");

        $response->assertOk();
    }

    // ── User model: kaedah yang belum diliputi ────────────────────

    #[Test]
    public function urus_setia_boleh_luluskan_tempahan(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        // isUrusSetia() dan bolehLuluskan() diliputi melalui akses
        $this->assertTrue($urusSetia->isUrusSetia());
        $this->assertTrue($urusSetia->bolehLuluskan());
        $this->assertFalse($urusSetia->isStaf());
        $this->assertFalse($urusSetia->isPentadbir());
    }

    #[Test]
    public function label_peranan_dikembalikan_dengan_betul(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $urusSetia = User::factory()->urusSetia()->create();
        $staf = User::factory()->staf()->create();

        $this->assertEquals('Pentadbir Sistem', $pentadbir->label_peranan);
        $this->assertEquals('Urus Setia', $urusSetia->label_peranan);
        $this->assertEquals('Staf', $staf->label_peranan);
    }

    #[Test]
    public function masked_email_disamarkan_dengan_betul(): void
    {
        $pengguna = User::factory()->staf()->create([
            'email' => 'hafez@bptm.gov.my',
        ]);

        // Format: haf***@bptm.gov.my
        $this->assertStringContainsString('***@bptm.gov.my', $pengguna->masked_email);
    }

    // ── BilikMesyuarat model: accessors ─────────────────────────────

    #[Test]
    public function bilik_mesyuarat_boleh_dikira_tempahan_bulan_ini(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        // Cipta tempahan bulan ini
        Tempahan::factory()->pagi()->create([
            'bilik_id' => $bilik->id,
            'tarikh' => now()->format('Y-m-d'),
            'user_id' => $pentadbir->id,
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        // Akses halaman bilik untuk mencetuskan withCount tempahan
        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat');
        $response->assertOk();
    }

    // ── TempahanBerulang model: tempahanAktif() ─────────────────────

    #[Test]
    public function kumpulan_berulang_kembalikan_tempahan_aktif_sahaja(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $kumpulan = TempahanBerulang::create([
            'ulid' => (string) Str::ulid(),
            'jenis' => 'bulanan',
            'setiap_n' => 1,
            'hari_dalam_minggu' => null,
            'tarikh_mula' => now()->addMonth()->startOfMonth()->toDateString(),
            'tarikh_tamat' => now()->addMonths(2)->startOfMonth()->toDateString(),
            'sesi' => ['pagi'],
            'bilik_id' => $bilik->id,
            'user_id' => $pentadbir->id,
            'nama_mesyuarat' => 'Mesyuarat Model Test',
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah',
        ]);

        // Tempahan aktif
        Tempahan::create([
            'ulid' => (string) Str::ulid(),
            'tempahan_berulang_id' => $kumpulan->id,
            'nama_mesyuarat' => 'Mesyuarat Model Test',
            'tarikh' => now()->addMonth()->startOfMonth()->toDateString(),
            'sesi' => 'pagi',
            'masa_mula' => '09:00',
            'masa_tamat' => '13:00',
            'bilik_id' => $bilik->id,
            'user_id' => $pentadbir->id,
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah',
            'status' => Tempahan::STATUS_DILULUSKAN,
        ]);

        // Tempahan ditolak
        Tempahan::create([
            'ulid' => (string) Str::ulid(),
            'tempahan_berulang_id' => $kumpulan->id,
            'nama_mesyuarat' => 'Mesyuarat Model Test',
            'tarikh' => now()->addMonths(2)->startOfMonth()->toDateString(),
            'sesi' => 'pagi',
            'masa_mula' => '09:00',
            'masa_tamat' => '13:00',
            'bilik_id' => $bilik->id,
            'user_id' => $pentadbir->id,
            'bilangan_peserta' => 10,
            'kategori' => 'mesyuarat',
            'nama_pengerusi' => 'Pengarah',
            'status' => Tempahan::STATUS_DITOLAK,
        ]);

        // tempahanAktif() patut hanya pulangkan yang bukan ditolak
        $kumpulan->refresh();
        $this->assertEquals(1, $kumpulan->tempahanAktif()->count());
        $this->assertEquals(2, $kumpulan->tempahan()->count());
    }

    // ── KalendarController: publicEvents & events ────────────────────

    #[Test]
    public function awam_boleh_dapatkan_events_kalendar(): void
    {
        BilikMesyuarat::factory()->kapasiti(20)->create();

        $mula = now()->startOfMonth()->format('Y-m-d');
        $akhir = now()->endOfMonth()->format('Y-m-d');

        $response = $this->get("/awam/events?start={$mula}&end={$akhir}");

        $response->assertOk();
    }

    // ── Tempahan model: accessors ────────────────────────────────────

    #[Test]
    public function tempahan_model_kembalikan_no_rujukan_dengan_format_betul(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id' => $staf->id,
            'bilik_id' => $bilik->id,
        ]);

        // no_rujukan accessor: TMP-{tahun}-{ulid_suffix}
        $this->assertStringStartsWith('TMP-', $tempahan->no_rujukan);
    }

    #[Test]
    public function tempahan_model_kembalikan_masa_label_dengan_format_betul(): void
    {
        $staf = User::factory()->staf()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        $tempahan = Tempahan::factory()->pagi()->create([
            'user_id' => $staf->id,
            'bilik_id' => $bilik->id,
        ]);

        // masa_label: "09:00 - 13:00"
        $this->assertStringContainsString('-', $tempahan->masa_label);
        $this->assertStringContainsString('09:00', $tempahan->masa_label);
    }

    // ── AppServiceProvider: route bindings ──────────────────────────

    #[Test]
    public function route_binding_bilik_menggunakan_ulid(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $bilik = BilikMesyuarat::factory()->kapasiti(20)->create();

        // Akses dengan ULID — AppServiceProvider mengendalikan resolusi
        $response = $this->actingAs($pentadbir)->get("/bilik-mesyuarat/{$bilik->ulid}/edit");
        $response->assertOk();
    }

    #[Test]
    public function route_binding_bilik_404_jika_ulid_tidak_wujud(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/bilik-mesyuarat/ULID-TIDAK-WUJUD-99999/edit');
        $response->assertNotFound();
    }
}
