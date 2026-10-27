<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesPeranTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_tidak_boleh_membuka_halaman_koordinator(): void
    {
        $this->masukSebagaiMahasiswa();

        foreach (['/dosen', '/mahasiswa', '/bidang-minat', '/mahasiswa/impor'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_dosen_tidak_boleh_membuka_halaman_koordinator(): void
    {
        $this->masukSebagaiDosen();

        $this->get('/dosen')->assertForbidden();
        $this->get('/mahasiswa')->assertForbidden();
    }

    public function test_koordinator_boleh_membuka_halaman_pengelolaan(): void
    {
        $this->masukSebagaiKoordinator();

        foreach (['/dosen', '/mahasiswa', '/bidang-minat', '/mahasiswa/impor', '/skripsi'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_beranda_menyesuaikan_peran(): void
    {
        $this->masukSebagaiKoordinator();
        $this->get('/')->assertOk()->assertSee('Ringkasan bimbingan skripsi');

        $this->masukSebagaiDosen();
        $this->get('/')->assertOk()->assertSee('Bimbingan menunggu pemeriksaan');

        $this->masukSebagaiMahasiswa();
        $this->get('/')->assertRedirect(route('skripsi.create'));
    }

    public function test_halaman_tidak_ada_menampilkan_halaman_404_berbahasa_indonesia(): void
    {
        $this->masukSebagaiKoordinator();

        $this->get('/tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }
}
