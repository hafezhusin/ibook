<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Kes Auth Lanjut — AuthController: login POST, logout, SSO, 2FA
 */
class AuthLanjutTest extends TestCase
{
    // ── Login POST ───────────────────────────────────────────────────

    #[Test]
    public function pengguna_nyahaktif_tidak_boleh_log_masuk(): void
    {
        User::factory()->nyahaktif()->create([
            'email' => 'nyahaktif@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'nyahaktif@bptm.gov.my',
            'password' => 'Rahsia@123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function login_gagal_jika_email_kosong(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'Rahsia@123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function login_gagal_jika_kata_laluan_kosong(): void
    {
        $response = $this->post('/login', [
            'email' => 'staf@bptm.gov.my',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function login_berjaya_rekod_last_login_at(): void
    {
        $pengguna = User::factory()->staf()->create([
            'email' => 'staf.login@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
            'aktif' => true,
            'last_login_at' => null,
        ]);

        $this->post('/login', [
            'email' => 'staf.login@bptm.gov.my',
            'password' => 'Rahsia@123',
        ]);

        $pengguna->refresh();
        $this->assertNotNull($pengguna->last_login_at);
    }

    #[Test]
    public function login_berjaya_redirect_ke_dashboard(): void
    {
        User::factory()->staf()->create([
            'email' => 'staf.redir@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
            'aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staf.redir@bptm.gov.my',
            'password' => 'Rahsia@123',
        ]);

        $response->assertRedirect('/');
    }

    #[Test]
    public function pengguna_log_masuk_diredirect_dari_halaman_login(): void
    {
        $pengguna = User::factory()->staf()->create();

        $response = $this->actingAs($pengguna)->get('/login');

        // showLogin() redirect ke dashboard jika sudah log masuk
        $response->assertRedirect(route('dashboard'));
    }

    // ── Logout ───────────────────────────────────────────────────────

    #[Test]
    public function pengguna_boleh_log_keluar(): void
    {
        $pengguna = User::factory()->staf()->create();

        $response = $this->actingAs($pengguna)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function selepas_logout_sesi_tidak_sah(): void
    {
        $pengguna = User::factory()->staf()->create();

        // Log masuk dulu, kemudian keluar
        $this->actingAs($pengguna)->post('/logout');

        // Percubaan akses dashboard selepas logout harus redirect ke login
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    // ── Google SSO Callback ──────────────────────────────────────────

    #[Test]
    public function sso_callback_gagal_domain_bukan_anm(): void
    {
        // Palsukan Socialite supaya kembalikan pengguna Google dengan domain salah
        $googleUser = (object) [
            'id' => 'google123',
            'name' => 'Penceroboh',
            'email' => 'penceroboh@gmail.com',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    #[Test]
    public function sso_callback_log_masuk_pengguna_sedia_ada(): void
    {
        $pengguna = User::factory()->staf()->create([
            'email' => 'staf.sso@anm.gov.my',
            'google_id' => 'google_id_sedia_ada',
            'aktif' => true,
        ]);

        $googleUser = (object) [
            'id' => 'google_id_sedia_ada',
            'name' => $pengguna->name,
            'email' => 'staf.sso@anm.gov.my',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($pengguna);
    }

    #[Test]
    public function sso_callback_gagal_jika_akaun_nyahaktif(): void
    {
        // Pengguna pernah log masuk (bukan null) tetapi dinyahaktifkan
        $pengguna = User::factory()->staf()->nyahaktif()->create([
            'email' => 'nyahaktif.sso@anm.gov.my',
            'google_id' => 'google_nyahaktif_123',
            'last_login_at' => now()->subDays(10),
        ]);

        $googleUser = (object) [
            'id' => 'google_nyahaktif_123',
            'name' => $pengguna->name,
            'email' => 'nyahaktif.sso@anm.gov.my',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    #[Test]
    public function sso_callback_daftar_pengguna_baharu_menunggu_kelulusan(): void
    {
        Mail::fake();

        $googleUser = (object) [
            'id' => 'google_baharu_99',
            'name' => 'Pekerja Baharu SSO',
            'email' => 'baharu.sso@anm.gov.my',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        // Redirect dengan warning (menunggu kelulusan)
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('warning');
        $this->assertGuest();

        // Pengguna baharu dicipta dengan aktif=false
        $this->assertDatabaseHas('users', [
            'email' => 'baharu.sso@anm.gov.my',
            'aktif' => false,
        ]);
    }

    #[Test]
    public function sso_callback_kemas_kini_google_id_akaun_lama(): void
    {
        // Akaun sedia ada (dicipta sebelum SSO) — tiada google_id
        $pengguna = User::factory()->staf()->create([
            'email' => 'lama.nossso@anm.gov.my',
            'google_id' => null,
            'aktif' => true,
        ]);

        $googleUser = (object) [
            'id' => 'google_id_baru_999',
            'name' => $pengguna->name,
            'email' => 'lama.nossso@anm.gov.my',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $this->get('/auth/google/callback');

        // google_id dikemaskini pada akaun lama
        $this->assertDatabaseHas('users', [
            'id' => $pengguna->id,
            'google_id' => 'google_id_baru_999',
        ]);
    }

    #[Test]
    public function sso_callback_gagal_jika_akaun_baharu_belum_disahkan(): void
    {
        // Pengguna SSO baharu dengan last_login_at = null (belum pernah log masuk)
        $pengguna = User::factory()->staf()->nyahaktif()->create([
            'email' => 'menunggu.sso@anm.gov.my',
            'google_id' => 'google_menunggu_555',
            'last_login_at' => null, // belum pernah log masuk
        ]);

        $googleUser = (object) [
            'id' => 'google_menunggu_555',
            'name' => $pengguna->name,
            'email' => 'menunggu.sso@anm.gov.my',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    // ── Login dengan 2FA ─────────────────────────────────────────────

    #[Test]
    public function login_dengan_2fa_aktif_redirect_ke_halaman_otp(): void
    {
        Mail::fake();

        $pengguna = User::factory()->staf()->create([
            'email' => 'staf.2fa@bptm.gov.my',
            'password' => bcrypt('Rahsia@123'),
            'aktif' => true,
            'dua_faktor_aktif' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staf.2fa@bptm.gov.my',
            'password' => 'Rahsia@123',
        ]);

        // Harus redirect ke halaman OTP, bukan dashboard
        $response->assertRedirect(route('dua-faktor.show'));
        $this->assertGuest(); // belum log masuk sepenuhnya
    }

    // ── 2FA Verify ───────────────────────────────────────────────────

    #[Test]
    public function halaman_2fa_redirect_login_jika_tiada_sesi(): void
    {
        $response = $this->get('/dua-faktor');

        // Tiada 2fa_user_id dalam sesi — redirect ke login
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function verify_2fa_berjaya_log_masuk_pengguna(): void
    {
        $pengguna = User::factory()->staf()->create();
        $otp = '123456';
        $expiry = now()->addMinutes(10);

        // Sediakan sesi dan cache OTP secara manual
        Cache::put('2fa_otp_'.$pengguna->id, [
            'kod_hash' => hash_hmac('sha256', $otp, config('app.key')),
            'percubaan' => 0,
            'expires_at' => $expiry->timestamp,
        ], $expiry);

        $response = $this->withSession(['2fa_user_id' => $pengguna->id, '2fa_remember' => false])
            ->post('/dua-faktor', ['kod' => $otp]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($pengguna);
    }

    #[Test]
    public function verify_2fa_gagal_dengan_kod_salah(): void
    {
        $pengguna = User::factory()->staf()->create();
        $otp = '123456';
        $expiry = now()->addMinutes(10);

        Cache::put('2fa_otp_'.$pengguna->id, [
            'kod_hash' => hash_hmac('sha256', $otp, config('app.key')),
            'percubaan' => 0,
            'expires_at' => $expiry->timestamp,
        ], $expiry);

        $response = $this->withSession(['2fa_user_id' => $pengguna->id, '2fa_remember' => false])
            ->post('/dua-faktor', ['kod' => '999999']); // kod salah

        $response->assertSessionHasErrors('kod');
        $this->assertGuest();
    }

    #[Test]
    public function verify_2fa_gagal_jika_otp_luput(): void
    {
        $pengguna = User::factory()->staf()->create();

        // Set expires_at ke masa lepas
        Cache::put('2fa_otp_'.$pengguna->id, [
            'kod_hash' => hash_hmac('sha256', '123456', config('app.key')),
            'percubaan' => 0,
            'expires_at' => now()->subMinutes(5)->timestamp, // sudah luput
        ], now()->addMinutes(10)); // cache TTL masih ada tapi expires_at sudah berlalu

        $response = $this->withSession(['2fa_user_id' => $pengguna->id, '2fa_remember' => false])
            ->post('/dua-faktor', ['kod' => '123456']);

        // Redirect ke login dengan mesej luput
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
