<?php

namespace App\Services;

use App\Enums\PeranUser;
use App\Exceptions\AturanBisnisException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pembuatan akun login untuk dosen dan mahasiswa. Semua akun baru memakai
 * kata sandi awal dari konfigurasi dan WAJIB menggantinya saat login pertama.
 */
class AkunService
{
    private function passwordAwal(): string
    {
        $password = (string) config('sibis.password_awal');

        if (strlen($password) < 8) {
            throw new AturanBisnisException(
                'SIBIS_PASSWORD_AWAL belum diatur di .env (minimal 8 karakter).'
            );
        }

        return $password;
    }

    /** @throws AturanBisnisException bila kata sandi awal belum dikonfigurasi */
    public function pastikanSiap(): void
    {
        $this->passwordAwal();
    }

    /** Hash sekali untuk dipakai ulang pada impor massal (bcrypt sengaja lambat). */
    public function hashAwal(): string
    {
        return Hash::make($this->passwordAwal());
    }

    public function buat(string $nama, string $email, PeranUser $peran): User
    {
        return User::create([
            'name' => $nama,
            'email' => Str::lower($email),
            'password' => $this->passwordAwal(),   // di-hash oleh cast 'hashed'
            'peran' => $peran,
            'wajib_ganti_password' => true,
        ]);
    }

    public function reset(User $user): void
    {
        $user->forceFill([
            'password' => $this->passwordAwal(),
            'wajib_ganti_password' => true,
            'remember_token' => Str::random(60),
        ])->save();
    }

    public function emailMahasiswa(string $nim, ?string $email): string
    {
        return Str::lower($email ?: $nim.'@'.config('sibis.domain_email_mahasiswa'));
    }
}
