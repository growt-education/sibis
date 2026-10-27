<?php

namespace App\Policies;

use App\Enums\PeranUser;
use App\Enums\StatusBimbingan;
use App\Models\Bimbingan;
use App\Models\User;

class BimbinganPolicy
{
    /** Aturan bisnis #5: hanya dosen yang bersangkutan, hanya yang masih menunggu. */
    public function periksa(User $user, Bimbingan $bimbingan): bool
    {
        return $user->peran === PeranUser::Dosen
            && $user->dosen !== null
            && $bimbingan->dosen_id === $user->dosen->id
            && $bimbingan->status === StatusBimbingan::Menunggu;
    }

    /** Draf boleh diunduh pemilik, kedua pembimbing, dan koordinator. */
    public function unduhDraf(User $user, Bimbingan $bimbingan): bool
    {
        return $user->can('view', $bimbingan->skripsi);
    }
}
