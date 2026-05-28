<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 10 — Sistem: Health Check & Dashboard
 */
class SystemTest extends TestCase
{
    #[Test]
    public function health_endpoint_kembalikan_status_ok(): void
    {
        // Token ditetapkan dalam phpunit.xml <env name="HEALTH_TOKEN" value="phpunit-health-token-test"/>
        $token = env('HEALTH_TOKEN');

        $response = $this->get('/health?token='.$token);

        // Respons diminimumkan — hanya status & timestamp (tiada butiran komponen)
        $response->assertOk()
            ->assertJsonStructure(['status', 'timestamp'])
            ->assertJson(['status' => 'ok'])
            ->assertJsonMissing(['db', 'disk', 'version']); // pastikan maklumat sensitif tidak terdedah
    }

    #[Test]
    public function health_endpoint_tolak_tanpa_token(): void
    {
        // Pastikan endpoint kembalikan 404 (bukan 401/403) tanpa token
        // supaya penggodam tidak tahu endpoint ini wujud
        $this->get('/health')->assertNotFound();
    }

    #[Test]
    public function health_endpoint_tolak_token_salah(): void
    {
        $this->get('/health?token=token-yang-salah-sama-sekali')->assertNotFound();
    }

    #[Test]
    public function dashboard_boleh_diakses_oleh_staf(): void
    {
        $staf = User::factory()->staf()->create();

        $response = $this->actingAs($staf)->get('/');

        $response->assertOk();
    }

    #[Test]
    public function dashboard_boleh_diakses_oleh_pentadbir(): void
    {
        $pentadbir = User::factory()->pentadbir()->create();

        $response = $this->actingAs($pentadbir)->get('/');

        $response->assertOk();
    }

    #[Test]
    public function dashboard_boleh_diakses_oleh_urus_setia(): void
    {
        $urusSetia = User::factory()->urusSetia()->create();

        $response = $this->actingAs($urusSetia)->get('/');

        $response->assertOk();
    }

    #[Test]
    public function tetamu_tidak_boleh_akses_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function pratonton_berulang_kembalikan_senarai_tarikh(): void
    {
        $staf = User::factory()->staf()->create();

        // GET params mesti dalam URL (bukan header) — pratonton = AJAX GET endpoint
        $url = '/tempahan-berulang/pratonton?'
            .'jenis=mingguan&setiap_n=1'
            .'&hari_dalam_minggu[]=1'   // Isnin
            .'&tarikh_mula=2026-06-01&tarikh_tamat=2026-06-30';

        $response = $this->actingAs($staf)->get($url);

        // Endpoint mengembalikan JSON dengan senarai tarikh
        $response->assertOk()
            ->assertJsonStructure(['tarikh', 'jumlah', 'had', 'tercapai_had']);
    }
}
