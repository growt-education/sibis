<?php

namespace Tests\Feature;

use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Skripsi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengelolaanDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_koordinator_menambah_dosen_beserta_akun(): void
    {
        $this->masukSebagaiKoordinator();
        $bidang = BidangMinat::factory()->create();

        $this->post('/dosen', [
            'nidn' => '0011223344', 'nama' => 'Dr. Contoh, M.Kom.', 'bidang_minat_id' => $bidang->id,
            'email' => 'Contoh@Kampus.AC.ID', 'no_hp' => '08123456789', 'kuota' => 8,
        ])->assertRedirect(route('dosen.index'));

        $dosen = Dosen::where('nidn', '0011223344')->firstOrFail();
        $user = User::findOrFail($dosen->user_id);

        $this->assertSame('contoh@kampus.ac.id', $user->email);
        $this->assertSame('dosen', $user->peran->value);
        $this->assertTrue($user->wajib_ganti_password);
        $this->assertTrue(Hash::check('RahasiaAwal123', $user->password));
    }

    public function test_validasi_dosen_berpesan_indonesia(): void
    {
        $this->masukSebagaiKoordinator();

        $this->post('/dosen', [])->assertSessionHasErrors(['nidn', 'nama', 'email', 'bidang_minat_id', 'kuota']);
        $this->assertSame('NIDN wajib diisi.', session('errors')->first('nidn'));
    }

    public function test_email_dosen_harus_unik_di_seluruh_akun(): void
    {
        $this->masukSebagaiKoordinator();
        $bidang = BidangMinat::factory()->create();
        $sudahAda = User::factory()->create();

        $this->post('/dosen', [
            'nidn' => '0011223344', 'nama' => 'Dr. Contoh', 'bidang_minat_id' => $bidang->id,
            'email' => $sudahAda->email, 'kuota' => 8,
        ])->assertSessionHasErrors('email');
    }

    public function test_dosen_yang_masih_membimbing_tidak_bisa_dihapus(): void
    {
        $this->masukSebagaiKoordinator();
        $dosen = Dosen::factory()->create();
        Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id]);

        $this->delete("/dosen/{$dosen->id}")->assertSessionHas('gagal');

        $this->assertModelExists($dosen);
    }

    public function test_dosen_tanpa_keterkaitan_bisa_dihapus_beserta_akunnya(): void
    {
        $this->masukSebagaiKoordinator();
        $user = User::factory()->dosen()->create();
        $dosen = Dosen::factory()->create(['user_id' => $user->id]);

        $this->delete("/dosen/{$dosen->id}")->assertRedirect(route('dosen.index'));

        $this->assertModelMissing($dosen);
        $this->assertModelMissing($user);
    }

    public function test_mahasiswa_tanpa_email_login_memakai_nim(): void
    {
        $this->masukSebagaiKoordinator();

        $this->post('/mahasiswa', ['nim' => '2022000123', 'nama' => 'Ani Lestari', 'angkatan' => 2022])
            ->assertRedirect(route('mahasiswa.index'));

        $mahasiswa = Mahasiswa::where('nim', '2022000123')->firstOrFail();
        $this->assertSame('2022000123@mhs.sibis.test', $mahasiswa->user->email);
        $this->assertNull($mahasiswa->email);
    }

    public function test_mahasiswa_yang_sudah_punya_skripsi_tidak_bisa_dihapus(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->create();

        $this->delete("/mahasiswa/{$skripsi->mahasiswa_id}")->assertSessionHas('gagal');

        $this->assertModelExists($skripsi->mahasiswa);
    }

    public function test_reset_kata_sandi_mewajibkan_ganti_lagi(): void
    {
        $this->masukSebagaiKoordinator();
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->post("/mahasiswa/{$mahasiswa->id}/reset-password")->assertSessionHas('sukses');

        $user->refresh();
        $this->assertTrue($user->wajib_ganti_password);
        $this->assertTrue(Hash::check('RahasiaAwal123', $user->password));
    }

    public function test_bidang_minat_crud_dan_perlindungan_penghapusan(): void
    {
        $this->masukSebagaiKoordinator();

        $this->post('/bidang-minat', ['nama' => 'Komputasi Awan'])->assertRedirect(route('bidang-minat.index'));
        $bidang = BidangMinat::where('nama', 'Komputasi Awan')->firstOrFail();

        $this->post('/bidang-minat', ['nama' => 'Komputasi Awan'])->assertSessionHasErrors('nama');

        $this->put("/bidang-minat/{$bidang->id}", ['nama' => 'Komputasi Awan dan Edge'])->assertRedirect();
        $this->assertSame('Komputasi Awan dan Edge', $bidang->fresh()->nama);

        Dosen::factory()->create(['bidang_minat_id' => $bidang->id]);
        $this->delete("/bidang-minat/{$bidang->id}")->assertSessionHas('gagal');
        $this->assertModelExists($bidang);
    }
}
