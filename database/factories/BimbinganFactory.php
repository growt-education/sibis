<?php

namespace Database\Factories;

use App\Enums\StatusBimbingan;
use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Skripsi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bimbingan> */
class BimbinganFactory extends Factory
{
    protected $model = Bimbingan::class;

    public function definition(): array
    {
        return [
            'skripsi_id' => Skripsi::factory(),
            'dosen_id' => Dosen::factory(),
            'tanggal' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'bab' => 'BAB I',
            'uraian' => 'Konsultasi '.fake()->sentence(),
            'status' => StatusBimbingan::Menunggu,
        ];
    }

    public function disetujui(): static
    {
        return $this->state(['status' => StatusBimbingan::Disetujui]);
    }
}
