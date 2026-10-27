<?php

namespace App\Policies;

use App\Enums\PeranUser;
use App\Enums\StatusSkripsi;
use App\Models\Skripsi;
use App\Models\User;

class SkripsiPolicy
{
    /** Semua pengguna login boleh membuka daftar; isinya disaring lewat scope dapatDilihatOleh. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Skripsi $skripsi): bool
    {
        return match ($user->peran) {
            PeranUser::Koordinator => true,
            PeranUser::Dosen => $skripsi->dibimbingOleh($user->dosen),
            PeranUser::Mahasiswa => $user->mahasiswa !== null
                && $skripsi->mahasiswa_id === $user->mahasiswa->id,
            default => false,
        };
    }

    /** Aturan bisnis #1: satu mahasiswa hanya boleh memiliki satu skripsi. */
    public function create(User $user): bool
    {
        return $user->peran === PeranUser::Mahasiswa
            && $user->mahasiswa !== null
            && ! $user->mahasiswa->skripsi()->exists();
    }

    public function kelola(User $user): bool
    {
        return $user->peran === PeranUser::Koordinator;
    }

    /** Aturan bisnis #4: hanya setelah pembimbing ditetapkan, oleh mahasiswa pemilik. */
    public function catatBimbingan(User $user, Skripsi $skripsi): bool
    {
        return $user->peran === PeranUser::Mahasiswa
            && $user->mahasiswa !== null
            && $skripsi->mahasiswa_id === $user->mahasiswa->id
            && $skripsi->status !== StatusSkripsi::Lulus;
    }
}
