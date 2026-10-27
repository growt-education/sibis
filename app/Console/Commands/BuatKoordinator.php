<?php

namespace App\Console\Commands;

use App\Enums\PeranUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class BuatKoordinator extends Command
{
    protected $signature = 'sibis:koordinator {email : Email login} {--nama=Koordinator Skripsi : Nama tampilan}';

    protected $description = 'Buat atau perbarui akun koordinator (untuk produksi, tanpa seeder demo)';

    public function handle(): int
    {
        $email = Str::lower($this->argument('email'));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Kata sandi (min. 8 karakter, huruf dan angka)');
        if ($password !== (string) $this->secret('Ulangi kata sandi')) {
            $this->error('Kata sandi tidak sama.');

            return self::FAILURE;
        }

        $v = Validator::make(['p' => $password], ['p' => [Password::min(8)->letters()->numbers()]]);
        if ($v->fails()) {
            $this->error($v->errors()->first());

            return self::FAILURE;
        }

        User::updateOrCreate(['email' => $email], [
            'name' => $this->option('nama'),
            'password' => $password,
            'peran' => PeranUser::Koordinator,
            'wajib_ganti_password' => false,
        ]);

        $this->info("Akun koordinator {$email} siap dipakai.");

        return self::SUCCESS;
    }
}
