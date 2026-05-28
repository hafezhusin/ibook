<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes 1 & 2 — Pengesahan Identiti (Authentication)
 */
class AuthTest extends TestCase
{
    #[Test]
    public function pengguna_aktif_boleh_log_masuk_dengan_kelayakan_betul(): void
    {
        $pengguna = User::factory()->create([
            'email' => 'staf@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
            'aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staf@bptm.gov.my',
            'password' => 'Rahsia@123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($pengguna);
    }

    #[Test]
    public function pengguna_tidak_boleh_log_masuk_dengan_kata_laluan_salah(): void
    {
        User::factory()->create([
            'email' => 'staf@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
            'aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staf@bptm.gov.my',
            'password' => 'kata-laluan-salah',
        ]);

        $response->assertRedirect();
        $this->assertGuest();
    }
}
