<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Skripsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembimbingTest extends TestCase
{
    use RefreshDatabase;

    private function tetapkan(Skripsi $skripsi, int $p1, int $p2)
    {
        return $this->patch("/skripsi/{$skripsi->id}/pembimbing", [
            'pembimbing_1_id' => $p1, 'pembimbing_2_id' => $p2, 'tanggal_sk' => '2026-10-01',
        ]);
    }

    public function test_koordinator_menetapkan_pembimbing(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->diajukan()->create();
        [$a, $b] = Dosen::factory()->count(2)->create();

        $this->tetapkan($skripsi, $a->id, $b->id)->assertSessionHas('sukses');

        $skripsi->refresh();
        $this->assertSame($a->id, $skripsi->pembimbing_1_id);
        $this->assertSame($b->id, $skripsi->pembimbing_2_id);
        $this->assertSame('bimbingan', $skripsi->status->value);
    }

    public function test_pembimbing_1_dan_2_tidak_boleh_sama(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->diajukan()->create();
        $dosen = Dosen::factory()->create();

        $this->tetapkan($skripsi, $dosen->id, $dosen->id)->assertSessionHasErrors('pembimbing_2_id');

        $this->assertNull($skripsi->fresh()->pembimbing_1_id);
    }

    public function test_dosen_dengan_kuota_penuh_ditolak(): void
    {
        $this->masukSebagaiKoordinator();
        $penuh = Dosen::factory()->create(['kuota' => 1]);
        Skripsi::factory()->create(['pembimbing_1_id' => $penuh->id]);   // memakai satu-satunya slot
        $baru = Skripsi::factory()->diajukan()->create();
        $lain = Dosen::factory()->create();

        $this->tetapkan($baru, $penuh->id, $lain->id)->assertSessionHas('gagal');

        $this->assertNull($baru->fresh()->pembimbing_1_id);
    }

    public function test_dosen_yang_sudah_membimbing_skripsi_ini_tidak_dihitung_dua_kali(): void
    {
        $this->masukSebagaiKoordinator();
        $dosenKuotaSatu = Dosen::factory()->create(['kuota' => 1]);
        $skripsi = Skripsi::factory()->create(['pembimbing_1_id' => $dosenKuotaSatu->id]);
        $pengganti = Dosen::factory()->create();

        // pembimbing 1 tetap sama; hanya pembimbing 2 diganti
        $this->tetapkan($skripsi, $dosenKuotaSatu->id, $pengganti->id)->assertSessionHas('sukses');

        $this->assertSame($pengganti->id, $skripsi->fresh()->pembimbing_2_id);
    }

    public function test_skripsi_lulus_membebaskan_kuota(): void
    {
        $this->masukSebagaiKoordinator();
        $dosen = Dosen::factory()->create(['kuota' => 1]);
        Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id, 'status' => 'lulus']);
        $baru = Skripsi::factory()->diajukan()->create();
        $lain = Dosen::factory()->create();

        $this->tetapkan($baru, $dosen->id, $lain->id)->assertSessionHas('sukses');
    }

    public function test_hanya_koordinator_yang_boleh_menetapkan(): void
    {
        $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->diajukan()->create();
        [$a, $b] = Dosen::factory()->count(2)->create();

        $this->tetapkan($skripsi, $a->id, $b->id)->assertForbidden();
    }

    public function test_seminar_hasil_butuh_minimal_bimbingan_disetujui(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->create();
        Bimbingan::factory()->count(3)->disetujui()
            ->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $skripsi->pembimbing_1_id]);

        $this->patch("/skripsi/{$skripsi->id}/status", ['status' => 'seminar_hasil'])
            ->assertSessionHas('gagal');
        $this->assertSame('bimbingan', $skripsi->fresh()->status->value);

        Bimbingan::factory()->count(5)->disetujui()
            ->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $skripsi->pembimbing_1_id]);

        $this->patch("/skripsi/{$skripsi->id}/status", ['status' => 'seminar_hasil'])
            ->assertSessionHas('sukses');
        $this->assertSame('seminar_hasil', $skripsi->fresh()->status->value);
    }

    public function test_status_selain_diajukan_butuh_pembimbing(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->diajukan()->create();

        $this->patch("/skripsi/{$skripsi->id}/status", ['status' => 'seminar_proposal'])
            ->assertSessionHas('gagal');
    }

    public function test_status_tidak_dikenal_ditolak(): void
    {
        $this->masukSebagaiKoordinator();
        $skripsi = Skripsi::factory()->create();

        $this->patch("/skripsi/{$skripsi->id}/status", ['status' => 'ngawur'])
            ->assertSessionHasErrors('status');
    }
}
