<?php

namespace Database\Factories;

use App\Enums\StatusSkripsi;
use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Skripsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Skripsi> */
class SkripsiFactory extends Factory
{
    protected $model = Skripsi::class;

    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'bidang_minat_id' => BidangMinat::factory(),
            'judul' => 'Sistem '.fake()->sentence(6),
            'ringkasan' => fake()->paragraph(),
            'pembimbing_1_id' => Dosen::factory(),
            'pembimbing_2_id' => Dosen::factory(),
            'status' => StatusSkripsi::Bimbingan,
            'tanggal_sk' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    /** Baru diajukan: belum ada pembimbing. */
    public function diajukan(): static
    {
        return $this->state([
            'pembimbing_1_id' => null,
            'pembimbing_2_id' => null,
            'tanggal_sk' => null,
            'status' => StatusSkripsi::Diajukan,
        ]);
    }
}
