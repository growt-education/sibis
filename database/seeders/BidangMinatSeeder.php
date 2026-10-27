<?php

namespace Database\Seeders;

use App\Models\BidangMinat;
use Illuminate\Database\Seeder;

class BidangMinatSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            'Rekayasa Perangkat Lunak', 'Sistem Cerdas', 'Jaringan dan Keamanan Komputer',
            'Sistem Informasi', 'Multimedia dan Game', 'Sains Data',
        ];

        foreach ($daftar as $nama) {
            BidangMinat::firstOrCreate(['nama' => $nama]);
        }
    }
}
