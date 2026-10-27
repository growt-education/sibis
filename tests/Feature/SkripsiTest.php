<?php

namespace Tests\Feature;

use App\Models\BidangMinat;
use App\Models\Skripsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkripsiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_mengajukan_judul(): void
    {
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $bidang = BidangMinat::factory()->create();

        $this->post('/skripsi', [
            'judul' => 'Sistem Informasi Bimbingan Skripsi Berbasis Web',
            'bidang_minat_id' => $bidang->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('skripsi', [
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'diajukan',
            'pembimbing_1_id' => null,
        ]);
    }

    public function test_satu_mahasiswa_hanya_boleh_satu_skripsi(): void
    {
        $mahasiswa = $this->masukSebagaiMahasiswa();
        Skripsi::factory()->diajukan()->create(['mahasiswa_id' => $mahasiswa->id]);
        $bidang = BidangMinat::factory()->create();

        $this->post('/skripsi', [
            'judul' => 'Judul Kedua Yang Cukup Panjang',
            'bidang_minat_id' => $bidang->id,
        ])->assertSessionHas('gagal');

        $this->assertSame(1, Skripsi::where('mahasiswa_id', $mahasiswa->id)->count());
    }

    public function test_judul_wajib_diisi_dengan_pesan_indonesia(): void
    {
        $this->masukSebagaiMahasiswa();

        $this->post('/skripsi', [])->assertSessionHasErrors(['judul', 'bidang_minat_id']);
        $this->assertSame('judul wajib diisi.', session('errors')->first('judul'));
    }

    public function test_mahasiswa_tidak_bisa_melihat_skripsi_orang_lain(): void
    {
        $this->masukSebagaiMahasiswa();
        $milikOrangLain = Skripsi::factory()->create();

        $this->get("/skripsi/{$milikOrangLain->id}")->assertForbidden();
    }

    public function test_mahasiswa_melihat_skripsinya_sendiri(): void
    {
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->get("/skripsi/{$skripsi->id}")->assertOk()->assertSee($skripsi->judul);
        $this->get('/')->assertRedirect(route('skripsi.show', $skripsi));
    }

    public function test_dosen_hanya_melihat_skripsi_bimbingannya(): void
    {
        $dosen = $this->masukSebagaiDosen();
        $milikku = Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id, 'judul' => 'Judul Bimbingan Saya']);
        $milikLain = Skripsi::factory()->create(['judul' => 'Judul Milik Dosen Lain']);

        $this->get("/skripsi/{$milikku->id}")->assertOk();
        $this->get("/skripsi/{$milikLain->id}")->assertForbidden();

        $this->get('/skripsi')->assertOk()
            ->assertSee('Judul Bimbingan Saya')
            ->assertDontSee('Judul Milik Dosen Lain');
    }

    public function test_koordinator_mencari_berdasarkan_judul_nama_dan_nim(): void
    {
        $this->masukSebagaiKoordinator();
        $target = Skripsi::factory()->create(['judul' => 'Sistem Pakar Diagnosis Penyakit Padi']);
        Skripsi::factory()->create(['judul' => 'Aplikasi Kasir Toko Kelontong']);

        $this->get('/skripsi?q=pakar')->assertOk()
            ->assertSee('Sistem Pakar Diagnosis')->assertDontSee('Kasir Toko');

        $this->get('/skripsi?q='.$target->mahasiswa->nim)->assertOk()
            ->assertSee('Sistem Pakar Diagnosis')->assertDontSee('Kasir Toko');

        $this->get('/skripsi?q='.urlencode(mb_strtoupper($target->mahasiswa->nama)))->assertOk()
            ->assertSee('Sistem Pakar Diagnosis');
    }

    public function test_karakter_khusus_pencarian_tidak_dianggap_wildcard(): void
    {
        $this->masukSebagaiKoordinator();
        Skripsi::factory()->count(2)->create();

        $response = $this->get('/skripsi?q=%25')->assertOk();

        $this->assertSame(0, $response->viewData('skripsi')->total());
    }

    public function test_daftar_skripsi_dipaginasi(): void
    {
        config(['sibis.per_halaman' => 5]);
        $this->masukSebagaiKoordinator();
        Skripsi::factory()->count(7)->create();

        $halaman1 = $this->get('/skripsi')->assertOk()->viewData('skripsi');
        $halaman2 = $this->get('/skripsi?page=2')->assertOk()->viewData('skripsi');

        $this->assertSame(7, $halaman1->total());
        $this->assertCount(5, $halaman1);
        $this->assertCount(2, $halaman2);
    }

    public function test_filter_status(): void
    {
        $this->masukSebagaiKoordinator();
        Skripsi::factory()->diajukan()->create(['judul' => 'Judul Baru Diajukan']);
        Skripsi::factory()->create(['judul' => 'Judul Sudah Bimbingan']);

        $this->get('/skripsi?status=diajukan')->assertOk()
            ->assertSee('Judul Baru Diajukan')->assertDontSee('Judul Sudah Bimbingan');
    }
}
