<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes Profil Lanjut — ProfilController: toggle2FA, tukar kata laluan, kemaskini jabatan
 */
class ProfilLanjutTest extends TestCase
{
    // ── toggle2FA ────────────────────────────────────────────────────

    #[Test]
    public function pengguna_boleh_aktifkan_pengesahan_dua_faktor(): void
    {
        $staf = User::factory()->staf()->create([
            'dua_faktor_aktif' => false,
        ]);

        $response = $this->actingAs($staf)->post('/profil/2fa-toggle');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $staf->refresh();
        $this->assertTrue($staf->dua_faktor_aktif);
    }

    #[Test]
    public function pengguna_boleh_nyahaktifkan_pengesahan_dua_faktor(): void
    {
        $staf = User::factory()->staf()->create([
            'dua_faktor_aktif' => true,
        ]);

        $response = $this->actingAs($staf)->post('/profil/2fa-toggle');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $staf->refresh();
        $this->assertFalse($staf->dua_faktor_aktif);
    }

    // ── updatePassword ───────────────────────────────────────────────

    #[Test]
    public function pengguna_boleh_tukar_kata_laluan_dengan_kelayakan_betul(): void
    {
        $staf = User::factory()->staf()->create([
            'password' => bcrypt('KataLaluanLama@123'),
            'google_id' => null,
            'email' => 'staf.tukar@bptm.gov.my',
        ]);

        $response = $this->actingAs($staf)->post('/profil/kata-laluan', [
            'kata_laluan_semasa' => 'KataLaluanLama@123',
            'password' => 'KataLaluanBaharu@456',
            'password_confirmation' => 'KataLaluanBaharu@456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success_password');
    }

    #[Test]
    public function tukar_kata_laluan_gagal_jika_kata_laluan_semasa_salah(): void
    {
        $staf = User::factory()->staf()->create([
            'password' => bcrypt('KataLaluanBetul@123'),
            'google_id' => null,
            'email' => 'staf.salah@bptm.gov.my',
        ]);

        $response = $this->actingAs($staf)->post('/profil/kata-laluan', [
            'kata_laluan_semasa' => 'KataLaluanSalah@999',
            'password' => 'KataLaluanBaharu@456',
            'password_confirmation' => 'KataLaluanBaharu@456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('kata_laluan_semasa');
    }

    #[Test]
    public function tukar_kata_laluan_gagal_jika_pengesahan_tidak_sepadan(): void
    {
        $staf = User::factory()->staf()->create([
            'password' => bcrypt('KataLaluanLama@123'),
            'google_id' => null,
        ]);

        $response = $this->actingAs($staf)->post('/profil/kata-laluan', [
            'kata_laluan_semasa' => 'KataLaluanLama@123',
            'password' => 'KataLaluanBaharu@456',
            'password_confirmation' => 'TidakSepadan@789',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function akaun_sso_tidak_boleh_tukar_kata_laluan(): void
    {
        // Pengguna SSO (ada google_id) tidak boleh tukar kata laluan sistem
        $ssoUser = User::factory()->staf()->create([
            'email' => 'staf.sso@anm.gov.my',
            'google_id' => 'google_id_xyz',
        ]);

        $response = $this->actingAs($ssoUser)->post('/profil/kata-laluan', [
            'kata_laluan_semasa' => 'ApaApa@123',
            'password' => 'KataLaluanBaharu@456',
            'password_confirmation' => 'KataLaluanBaharu@456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_password');
    }

    // ── update profil ────────────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_kemaskini_jabatan_sendiri(): void
    {
        // Pentadbir (bukan staf) boleh kemaskini jabatan
        $pentadbir = User::factory()->pentadbir()->create([
            'jabatan' => 'Unit Lama',
            'google_id' => null,
            'email' => 'pentadbir.update@bptm.gov.my',
        ]);

        // Pentadbir bukan SSO (tiada google_id, email bukan @anm.gov.my),
        // jadi 'name' juga wajib dihantar
        $response = $this->actingAs($pentadbir)->post('/profil/kemaskini', [
            'name' => $pentadbir->name,
            'jabatan' => 'Unit Aplikasi Gunasama',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $pentadbir->id,
            'jabatan' => 'Unit Aplikasi Gunasama',
        ]);
    }

    #[Test]
    public function staf_tidak_boleh_kemaskini_jabatan_melalui_profil(): void
    {
        // Staf tidak boleh kemas kini jabatan — controller buang medan itu
        $staf = User::factory()->staf()->create([
            'jabatan' => 'Unit Asal',
            'google_id' => null,
        ]);

        $response = $this->actingAs($staf)->post('/profil/kemaskini', [
            'name' => $staf->name,
            'jabatan' => 'Unit Cuba Tukar',
        ]);

        $response->assertRedirect();

        // Jabatan TIDAK berubah — controller buang medan jabatan untuk staf
        $this->assertDatabaseHas('users', [
            'id' => $staf->id,
            'jabatan' => 'Unit Asal',
        ]);
    }
}
