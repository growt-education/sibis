<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dialihkan_ke_halaman_login(): void
    {
        $this->get('/skripsi')->assertRedirect('/login');
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_berhasil(): void
    {
        $user = User::factory()->koordinator()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('beranda'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_dengan_kata_sandi_salah_ditolak(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_lima_kali_gagal(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->masukSebagaiKoordinator();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_kata_sandi_awal_wajib_diganti_sebelum_memakai_aplikasi(): void
    {
        $user = User::factory()->koordinator()->wajibGantiPassword()->create();
        $this->actingAs($user);

        $this->get('/')->assertRedirect(route('akun.password'));
        $this->get('/skripsi')->assertRedirect(route('akun.password'));
        $this->get('/akun/password')->assertOk();
    }

    public function test_ganti_kata_sandi_berhasil_dan_menghapus_kewajiban(): void
    {
        $user = User::factory()->koordinator()->wajibGantiPassword()->create();
        $this->actingAs($user);

        $this->put('/akun/password', [
            'password_lama' => 'password',
            'password' => 'BaruAman123',
            'password_confirmation' => 'BaruAman123',
        ])->assertRedirect(route('beranda'));

        $user->refresh();
        $this->assertFalse($user->wajib_ganti_password);
        $this->assertTrue(Hash::check('BaruAman123', $user->password));
    }

    public function test_kata_sandi_baru_harus_kuat_dan_berbeda(): void
    {
        $this->actingAs(User::factory()->create());

        $this->put('/akun/password', [
            'password_lama' => 'password',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->put('/akun/password', [
            'password_lama' => 'salah',
            'password' => 'BaruAman123',
            'password_confirmation' => 'BaruAman123',
        ])->assertSessionHasErrors('password_lama');
    }
}
