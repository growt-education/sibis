<?php

namespace Tests;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function masukSebagaiKoordinator(): User
    {
        $user = User::factory()->koordinator()->create();
        $this->actingAs($user);

        return $user;
    }

    protected function masukSebagaiDosen(array $atribut = []): Dosen
    {
        $user = User::factory()->dosen()->create();
        $dosen = Dosen::factory()->create(['user_id' => $user->id, ...$atribut]);
        $this->actingAs($user);

        return $dosen;
    }

    protected function masukSebagaiMahasiswa(): Mahasiswa
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        return $mahasiswa;
    }
}
