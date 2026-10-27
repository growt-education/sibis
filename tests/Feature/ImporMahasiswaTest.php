<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImporMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    private function berkas(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('mahasiswa.csv', $isi);
    }

    public function test_impor_csv_membuat_mahasiswa_dan_akun(): void
    {
        Storage::fake('local');
        $this->masukSebagaiKoordinator();

        $csv = "nim,nama,angkatan,email,no_hp\n"
            ."2021000001,Budi Santoso,2021,budi@contoh.id,08123456\n"
            ."2021000002,Siti Aminah,2021,,\n"
            ."XX,Baris Salah,1999,,\n";

        $respons = $this->post('/mahasiswa/impor', ['berkas' => $this->berkas($csv)])->assertRedirect();

        $this->assertSame(2, Mahasiswa::count());

        $budi = Mahasiswa::where('nim', '2021000001')->firstOrFail();
        $this->assertSame('budi@contoh.id', $budi->email);

        $siti = Mahasiswa::where('nim', '2021000002')->firstOrFail();
        $this->assertNull($siti->email);

        $akun = User::where('email', '2021000002@mhs.sibis.test')->firstOrFail();
        $this->assertSame($akun->id, $siti->user_id);
        $this->assertTrue($akun->wajib_ganti_password);
        $this->assertSame('mahasiswa', $akun->peran->value);
        $this->assertTrue(Hash::check('RahasiaAwal123', $akun->password));

        $this->get($respons->headers->get('Location'))
            ->assertOk()->assertSee('Impor selesai')->assertSee('Baris 4');
    }

    public function test_nim_yang_sudah_ada_dilewati(): void
    {
        Storage::fake('local');
        $this->masukSebagaiKoordinator();
        $csv = "nim;nama;angkatan\n2021000001;Budi Santoso;2021\n";

        $this->post('/mahasiswa/impor', ['berkas' => $this->berkas($csv)]);
        $this->post('/mahasiswa/impor', ['berkas' => $this->berkas($csv)]);

        $this->assertSame(1, Mahasiswa::count());
        $this->assertSame(1, User::where('peran', 'mahasiswa')->count());
    }

    public function test_header_tidak_lengkap_ditolak(): void
    {
        Storage::fake('local');
        $this->masukSebagaiKoordinator();

        $respons = $this->post('/mahasiswa/impor', ['berkas' => $this->berkas("nama,angkatan\nBudi,2021\n")]);

        $this->assertSame(0, Mahasiswa::count());
        $this->get($respons->headers->get('Location'))->assertOk()->assertSee('Impor gagal');
    }

    public function test_hanya_koordinator_yang_boleh_mengimpor(): void
    {
        $this->masukSebagaiMahasiswa();

        $this->post('/mahasiswa/impor', ['berkas' => $this->berkas("nim,nama,angkatan\n2021000001,A,2021\n")])
            ->assertForbidden();
    }

    public function test_impor_ditolak_bila_kata_sandi_awal_belum_diatur(): void
    {
        config(['sibis.password_awal' => null]);
        $this->masukSebagaiKoordinator();

        $this->post('/mahasiswa/impor', ['berkas' => $this->berkas("nim,nama,angkatan\n2021000001,A,2021\n")])
            ->assertSessionHas('gagal');
        $this->assertSame(0, Mahasiswa::count());
    }
}
