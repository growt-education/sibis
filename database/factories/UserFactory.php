<?php

namespace Database\Factories;

use App\Enums\PeranUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'peran' => PeranUser::Mahasiswa,
            'wajib_ganti_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function koordinator(): static
    {
        return $this->state(['peran' => PeranUser::Koordinator]);
    }

    public function dosen(): static
    {
        return $this->state(['peran' => PeranUser::Dosen]);
    }

    public function mahasiswa(): static
    {
        return $this->state(['peran' => PeranUser::Mahasiswa]);
    }

    public function wajibGantiPassword(): static
    {
        return $this->state(['wajib_ganti_password' => true]);
    }
}
