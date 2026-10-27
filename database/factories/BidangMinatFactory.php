<?php

namespace Database\Factories;

use App\Models\BidangMinat;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BidangMinat> */
class BidangMinatFactory extends Factory
{
    protected $model = BidangMinat::class;

    public function definition(): array
    {
        return ['nama' => fake()->unique()->words(3, true)];
    }
}
