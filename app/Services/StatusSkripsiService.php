<?php

namespace App\Services;

use App\Enums\StatusBimbingan;
use App\Enums\StatusSkripsi;
use App\Exceptions\AturanBisnisException;
use App\Models\Skripsi;
use Illuminate\Support\Facades\DB;

class StatusSkripsiService
{
    /**
     * Aturan bisnis #6: seminar hasil (dan tahap sesudahnya) membutuhkan minimal
     * N bimbingan disetujui. Tahap setelah "diajukan" membutuhkan pembimbing.
     *
     * @throws AturanBisnisException
     */
    public function ubah(Skripsi $skripsi, StatusSkripsi $baru): Skripsi
    {
        return DB::transaction(function () use ($skripsi, $baru) {
            $s = Skripsi::query()->whereKey($skripsi->id)->lockForUpdate()->firstOrFail();

            if ($baru !== StatusSkripsi::Diajukan && ! $s->punyaPembimbing()) {
                throw new AturanBisnisException('Tetapkan pembimbing 1 dan 2 sebelum mengubah status.');
            }

            $tahapSeminarHasilKeAtas = [
                StatusSkripsi::SeminarHasil, StatusSkripsi::Ujian, StatusSkripsi::Lulus,
            ];

            if (in_array($baru, $tahapSeminarHasilKeAtas, true)) {
                $disetujui = $s->bimbingan()->where('status', StatusBimbingan::Disetujui->value)->count();
                if ($disetujui < Skripsi::minBimbingan()) {
                    throw new AturanBisnisException(
                        'Bimbingan disetujui baru '.$disetujui.' dari minimal '.Skripsi::minBimbingan().' kali.'
                    );
                }
            }

            $s->update(['status' => $baru]);

            return $s;
        }, attempts: 3);
    }
}
