<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 14 — Laporan, Log Audit & Keselamatan Profil
 *
 * Meliputi kawasan berisiko tinggi yang belum diuji:
 *  - Halaman laporan (staf vs pentadbir — paparan berbeza)
 *  - RBAC eksport laporan (pentadbir/urus_setia sahaja)
 *  - RBAC log audit (pentadbir sahaja)
 *  - Tukar kata laluan profil (semak kata laluan semasa, hashing)
 *  - Toggle 2FA profil
 */
class LaporanAuditTest extends TestCase
{
    // ── LaporanController ────────────────────────────────────────────

    #[Test]
    public function staf_boleh_akses_halaman_laporan(): void
    {
        $staf = User::factory()->staf()->create(['jabatan' => 'Unit Aplikasi Gunasama']);

        $response = $this->actingAs($staf)->get('/laporan');

        $response->assertOk();
    }

    #[Test]
    public function pentadbir_boleh_akses_halaman_laporan(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/laporan');

        $response->assertOk();
    }

    #[Test]
    public function urus_setia_boleh_eksport_laporan_excel(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/laporan/eksport/excel');

        // Excel download — assertOk() atau assertSuccessful()
        $response->assertSuccessful();
    }

    #[Test]
    public function staf_tidak_boleh_eksport_laporan_excel(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/laporan/eksport/excel');

        $response->assertForbidden();
    }

    // ── AuditLogController ───────────────────────────────────────────

    #[Test]
    public function pentadbir_boleh_akses_log_audit(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/log-audit');

        $response->assertOk();
    }

    #[Test]
    public function staf_tidak_boleh_akses_log_audit(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/log-audit');

        $response->assertForbidden();
    }

    // ── ProfilController: kata laluan & 2FA ──────────────────────────

    #[Test]
    public function pengguna_boleh_tukar_kata_laluan_profil(): void
    {
        $staf = User::factory()->staf()->create([
            'password'  => bcrypt('KataLaluan@Lama1'),
            'google_id' => null,
        ]);

        $response = $this->actingAs($staf)->post('/profil/kata-laluan', [
            'kata_laluan_semasa'      => 'KataLaluan@Lama1',
            'password'                => 'KataLaluanBaru@2!',
            'password_confirmation'   => 'KataLaluanBaru@2!',
        ]);

        // ProfilController::updatePassword() pulang back() selepas berjaya
        $response->assertRedirect();
        $response->assertSessionHas('success_password');
    }

    #[Test]
    public function tukar_kata_laluan_gagal_jika_semasa_salah(): void
    {
        $staf = User::factory()->staf()->create([
            'password'  => bcrypt('KataLaluan@Betul1'),
            'google_id' => null,
        ]);

        $response = $this->actingAs($staf)->post('/profil/kata-laluan', [
            'kata_laluan_semasa'      => 'KataLaluanSalah!',
            'password'                => 'KataLaluanBaru@2!',
            'password_confirmation'   => 'KataLaluanBaru@2!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('kata_laluan_semasa');
    }

    #[Test]
    public function pengguna_boleh_toggle_2fa(): void
    {
        $staf = User::factory()->staf()->create(['dua_faktor_aktif' => false]);

        $response = $this->actingAs($staf)->post('/profil/2fa-toggle');

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'               => $staf->id,
            'dua_faktor_aktif' => true,
        ]);

        // Toggle sekali lagi — nyahaktifkan
        $this->actingAs($staf)->post('/profil/2fa-toggle');
        $this->assertDatabaseHas('users', [
            'id'               => $staf->id,
            'dua_faktor_aktif' => false,
        ]);
    }
}
