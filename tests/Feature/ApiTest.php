<?php

namespace Tests\Feature;

use App\Models\BidangMinat;
use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Skripsi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_skripsi_publik_berformat_json_tanpa_data_pribadi(): void
    {
        $skripsi = Skripsi::factory()->count(3)->create();

        $respons = $this->getJson('/api/skripsi')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => ['*' => ['id', 'judul', 'bidang_minat', 'status', 'tahun']]]);

        $respons->assertDontSee($skripsi->first()->mahasiswa->nim);
        $respons->assertDontSee($skripsi->first()->mahasiswa->nama);
    }

    public function test_pencarian_api_hanya_pada_judul(): void
    {
        $a = Skripsi::factory()->create(['judul' => 'Sistem Pakar Diagnosis Hama']);
        Skripsi::factory()->create(['judul' => 'Aplikasi Kasir Digital']);

        $this->getJson('/api/skripsi?q=pakar')->assertOk()->assertJsonCount(1, 'data');

        // NIM mahasiswa tidak boleh bisa ditelusuri lewat API publik
        $this->getJson('/api/skripsi?q='.$a->mahasiswa->nim)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_jumlah_per_halaman_dibatasi(): void
    {
        Skripsi::factory()->count(2)->create();

        $this->getJson('/api/skripsi?per_page=9999')->assertOk()->assertJsonPath('meta.per_page', 50);
    }

    public function test_daftar_dosen_memuat_sisa_kuota(): void
    {
        $dosen = Dosen::factory()->create(['kuota' => 8]);
        Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id]);

        $data = collect($this->getJson('/api/dosen')->assertOk()->json('data'));

        $this->assertSame(7, $data->firstWhere('id', $dosen->id)['sisa_kuota']);
        $this->assertSame(8, $data->firstWhere('id', $dosen->id)['kuota']);
    }

    public function test_skripsi_per_bidang_minat(): void
    {
        $bidang = BidangMinat::factory()->create();
        Skripsi::factory()->count(2)->create(['bidang_minat_id' => $bidang->id]);
        Skripsi::factory()->create();

        $this->getJson("/api/bidang-minat/{$bidang->id}/skripsi")->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/bidang-minat/99999/skripsi')->assertNotFound();
    }

    public function test_riwayat_bimbingan_butuh_token(): void
    {
        $this->getJson('/api/saya/bimbingan')->assertUnauthorized();
    }

    public function test_mahasiswa_melihat_riwayat_bimbingannya(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);
        Bimbingan::factory()->count(2)->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $skripsi->pembimbing_1_id]);
        Bimbingan::factory()->create();   // milik orang lain

        Sanctum::actingAs($user);

        $this->getJson('/api/saya/bimbingan')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => ['*' => ['tanggal', 'bab', 'dosen', 'status', 'catatan_dosen']]]);
    }

    public function test_mahasiswa_tanpa_skripsi_mendapat_404(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/saya/bimbingan')->assertNotFound();
    }

    public function test_menukar_kata_sandi_dengan_token(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $this->postJson('/api/token', ['email' => $user->email, 'password' => 'password'])
            ->assertCreated()
            ->assertJsonStructure(['token', 'tipe', 'kedaluwarsa']);

        $this->postJson('/api/token', ['email' => $user->email, 'password' => 'salah'])
            ->assertUnprocessable();
    }

    public function test_token_tidak_diberikan_selama_kata_sandi_awal_belum_diganti(): void
    {
        $user = User::factory()->mahasiswa()->wajibGantiPassword()->create();

        $this->postJson('/api/token', ['email' => $user->email, 'password' => 'password'])->assertForbidden();
    }
}
