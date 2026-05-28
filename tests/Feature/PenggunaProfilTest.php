<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 12 — Pengurusan Pengguna & Profil
 */
class PenggunaProfilTest extends TestCase
{
    // ── PenggunaController ───────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_akses_senarai_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        User::factory()->staf()->count(3)->create();

        $response = $this->actingAs($pentadbir)->get('/pengguna');

        $response->assertOk();
    }

    #[Test]
    public function staf_tidak_boleh_akses_senarai_pengguna(): void
    {
        // Route /pengguna dibuka untuk pentadbir_sistem DAN urus_setia.
        // Hanya staf yang dilarang masuk.
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/pengguna');

        $response->assertForbidden();
    }

    #[Test]
    public function pentadbir_boleh_tambah_pengguna_baharu(): void
    {
        // StorePenggunaRequest menggunakan ->uncompromised() yang menghubungi HIBP API.
        // Kita palsukan respons supaya kata laluan dianggap selamat dalam persekitaran ujian.
        Http::fake(['*pwnedpasswords.com/*' => Http::response('', 200)]);

        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->post('/pengguna', [
            'name' => 'Pekerja Baharu',
            'email' => 'baharu@anm.gov.my',
            'jabatan' => 'Unit Aplikasi Gunasama',
            'peranan' => User::PERANAN_STAF,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/pengguna');
        $this->assertDatabaseHas('users', [
            'email' => 'baharu@anm.gov.my',
            'peranan' => User::PERANAN_STAF,
            'aktif' => true,
        ]);
    }

    #[Test]
    public function pentadbir_boleh_toggle_aktif_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf = User::factory()->staf()->create(['aktif' => true]);

        // toggleAktif() mensyaratkan 'sebab' apabila pengguna sedang aktif.
        $response = $this->actingAs($pentadbir)->post("/pengguna/{$staf->id}/toggle-aktif", [
            'sebab' => 'Dinyahaktifkan untuk tujuan ujian',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $staf->id,
            'aktif' => false, // dinyahaktifkan
        ]);
    }

    #[Test]
    public function pentadbir_boleh_kemaskini_maklumat_pengguna(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit Lama']);

        $response = $this->actingAs($pentadbir)->put("/pengguna/{$staf->id}", [
            'name' => $staf->name,
            'email' => $staf->email,
            'jabatan' => 'Unit Baharu',
            'peranan' => User::PERANAN_STAF,
        ]);

        $response->assertRedirect('/pengguna');
        $this->assertDatabaseHas('users', [
            'id' => $staf->id,
            'jabatan' => 'Unit Baharu',
        ]);
    }

    // ── ProfilController ─────────────────────────────────────────────

    #[Test]
    public function pengguna_log_masuk_boleh_akses_profil_sendiri(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/profil');

        $response->assertOk();
    }

    #[Test]
    public function pengguna_boleh_kemaskini_nama_profil(): void
    {
        // ProfilController::update() menggunakan back() bukan redirect('/profil').
        // Staf tidak boleh kemas kini jabatan sendiri — controller buang medan itu.
        // Hanya nama yang boleh dikemaskini untuk pengguna bukan-SSO.
        $staf = User::factory()->staf()->create([
            'jabatan' => 'Unit Lama',
            'google_id' => null,
        ]);

        $response = $this->actingAs($staf)->post('/profil/kemaskini', [
            'name' => 'Nama Dikemaskini',
        ]);

        $response->assertRedirect(); // back() — redirect ke mana-mana URL sebelumnya
        $this->assertDatabaseHas('users', [
            'id' => $staf->id,
            'name' => 'Nama Dikemaskini',
        ]);
    }
}
