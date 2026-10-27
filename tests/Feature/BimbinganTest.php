<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Skripsi;
use App\Models\User;
use App\Notifications\BimbinganBaru;
use App\Notifications\BimbinganDiperiksa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BimbinganTest extends TestCase
{
    use RefreshDatabase;

    private function data(Skripsi $skripsi, array $ubah = []): array
    {
        return [
            'dosen_id' => $skripsi->pembimbing_1_id,
            'tanggal' => now()->toDateString(),
            'bab' => 'BAB III Metodologi',
            'uraian' => 'Membahas rancangan penelitian.',
            ...$ubah,
        ];
    }

    public function test_mahasiswa_mencatat_bimbingan_dengan_draf_pdf(): void
    {
        Storage::fake('local');
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi, [
            'file_draf' => UploadedFile::fake()->create('draf.pdf', 200, 'application/pdf'),
        ]))->assertSessionHas('sukses');

        $bimbingan = Bimbingan::firstOrFail();
        $this->assertSame('menunggu', $bimbingan->status->value);
        $this->assertNotNull($bimbingan->file_draf);
        Storage::disk('local')->assertExists($bimbingan->file_draf);
    }

    public function test_dosen_diberi_tahu_saat_bimbingan_baru(): void
    {
        Notification::fake();
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $userDosen = User::factory()->dosen()->create();
        $dosen = Dosen::factory()->create(['user_id' => $userDosen->id]);
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id, 'pembimbing_1_id' => $dosen->id]);

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi))->assertSessionHas('sukses');

        Notification::assertSentTo($userDosen, BimbinganBaru::class);
    }

    public function test_bimbingan_ditolak_sebelum_pembimbing_ditetapkan(): void
    {
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->diajukan()->create(['mahasiswa_id' => $mahasiswa->id]);
        $dosen = Dosen::factory()->create();

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi, ['dosen_id' => $dosen->id]))
            ->assertSessionHasErrors('dosen_id');

        $this->assertSame(0, Bimbingan::count());
    }

    public function test_bimbingan_hanya_dengan_pembimbing_sendiri(): void
    {
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);
        $bukanPembimbing = Dosen::factory()->create();

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi, ['dosen_id' => $bukanPembimbing->id]))
            ->assertSessionHasErrors('dosen_id');
    }

    public function test_mahasiswa_tidak_bisa_mencatat_bimbingan_di_skripsi_orang_lain(): void
    {
        $this->masukSebagaiMahasiswa();
        $milikLain = Skripsi::factory()->create();

        $this->post("/skripsi/{$milikLain->id}/bimbingan", $this->data($milikLain))->assertForbidden();
    }

    public function test_draf_harus_pdf_dan_tanggal_tidak_boleh_masa_depan(): void
    {
        Storage::fake('local');
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi, [
            'file_draf' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        ]))->assertSessionHasErrors('file_draf');

        $this->post("/skripsi/{$skripsi->id}/bimbingan", $this->data($skripsi, [
            'tanggal' => now()->addDays(3)->toDateString(),
        ]))->assertSessionHasErrors('tanggal');

        $this->assertSame(0, Bimbingan::count());
    }

    public function test_dosen_menyetujui_bimbingan_dan_mahasiswa_diberi_tahu(): void
    {
        Notification::fake();
        $dosen = $this->masukSebagaiDosen();
        $userMahasiswa = User::factory()->mahasiswa()->create();
        $mahasiswa = \App\Models\Mahasiswa::factory()->create(['user_id' => $userMahasiswa->id]);
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id, 'pembimbing_1_id' => $dosen->id]);
        $bimbingan = Bimbingan::factory()->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $dosen->id]);

        $this->patch("/bimbingan/{$bimbingan->id}/periksa", ['status' => 'disetujui'])
            ->assertSessionHas('sukses');

        $this->assertSame('disetujui', $bimbingan->fresh()->status->value);
        Notification::assertSentTo($userMahasiswa, BimbinganDiperiksa::class);
    }

    public function test_revisi_wajib_disertai_catatan(): void
    {
        $dosen = $this->masukSebagaiDosen();
        $skripsi = Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id]);
        $bimbingan = Bimbingan::factory()->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $dosen->id]);

        $this->patch("/bimbingan/{$bimbingan->id}/periksa", ['status' => 'revisi'])
            ->assertSessionHasErrors('catatan_dosen');

        $this->patch("/bimbingan/{$bimbingan->id}/periksa", ['status' => 'revisi', 'catatan_dosen' => 'Perbaiki rumusan masalah.'])
            ->assertSessionHas('sukses');

        $this->assertSame('revisi', $bimbingan->fresh()->status->value);
    }

    public function test_dosen_lain_tidak_boleh_memeriksa(): void
    {
        $this->masukSebagaiDosen();
        $bimbingan = Bimbingan::factory()->create();

        $this->patch("/bimbingan/{$bimbingan->id}/periksa", ['status' => 'disetujui'])->assertForbidden();
    }

    public function test_bimbingan_yang_sudah_diperiksa_tidak_dapat_diperiksa_ulang(): void
    {
        $dosen = $this->masukSebagaiDosen();
        $skripsi = Skripsi::factory()->create(['pembimbing_1_id' => $dosen->id]);
        $bimbingan = Bimbingan::factory()->disetujui()->create(['skripsi_id' => $skripsi->id, 'dosen_id' => $dosen->id]);

        $this->patch("/bimbingan/{$bimbingan->id}/periksa", ['status' => 'revisi', 'catatan_dosen' => 'x'])
            ->assertForbidden();
    }

    public function test_hanya_pihak_berkepentingan_yang_bisa_mengunduh_draf(): void
    {
        Storage::fake('local');
        $mahasiswa = $this->masukSebagaiMahasiswa();
        $skripsi = Skripsi::factory()->create(['mahasiswa_id' => $mahasiswa->id]);
        Storage::disk('local')->put('draf/uji.pdf', '%PDF-1.4 contoh');
        $bimbingan = Bimbingan::factory()->create([
            'skripsi_id' => $skripsi->id, 'dosen_id' => $skripsi->pembimbing_1_id, 'file_draf' => 'draf/uji.pdf',
        ]);

        // pemilik
        $this->get("/bimbingan/{$bimbingan->id}/draf")->assertOk();

        // mahasiswa lain
        $this->masukSebagaiMahasiswa();
        $this->get("/bimbingan/{$bimbingan->id}/draf")->assertForbidden();

        // dosen yang bukan pembimbing
        $this->masukSebagaiDosen();
        $this->get("/bimbingan/{$bimbingan->id}/draf")->assertForbidden();

        // koordinator
        $this->masukSebagaiKoordinator();
        $this->get("/bimbingan/{$bimbingan->id}/draf")->assertOk();
    }

    public function test_draf_tidak_bisa_diunduh_tamu(): void
    {
        $bimbingan = Bimbingan::factory()->create(['file_draf' => 'draf/uji.pdf']);

        $this->get("/bimbingan/{$bimbingan->id}/draf")->assertRedirect('/login');
    }
}
