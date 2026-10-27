<?php

namespace Database\Factories;

use App\Models\BidangMinat;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Dosen> */
class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        return [
            'nidn' => fake()->unique()->numerify('00########'),
            'nama' => fake()->name().', M.Kom.',
            'bidang_minat_id' => BidangMinat::factory(),
            'email' => fake()->unique()->safeEmail(),
            'no_hp' => fake()->numerify('08##########'),
            'kuota' => 8,
        ];
    }
}
