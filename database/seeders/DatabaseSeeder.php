<?php

namespace Database\Seeders;

use App\Enums\PeranUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BidangMinatSeeder::class);

        if (app()->isProduction()) {
            $this->command?->warn('Produksi: hanya bidang minat yang diisi. Buat koordinator dengan: php artisan sibis:koordinator email@kampus.ac.id');

            return;
        }

        User::updateOrCreate(['email' => 'koordinator@sibis.test'], [
            'name' => 'Koordinator Skripsi',
            'password' => 'password',
            'peran' => PeranUser::Koordinator,
            'wajib_ganti_password' => false,
        ]);

        // Ubah angkanya untuk uji beban, mis.: php artisan sibis:demo --dosen=60 --mahasiswa=5000 --kuota=10
        $this->command?->call('sibis:demo', ['--dosen' => 12, '--mahasiswa' => 20]);
    }
}
