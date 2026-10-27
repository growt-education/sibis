<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mahasiswa> */
class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        return [
            'nim' => fake()->unique()->numerify('20##########'),
            'nama' => fake()->name(),
            'angkatan' => fake()->numberBetween(2020, 2023),
            'no_hp' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
