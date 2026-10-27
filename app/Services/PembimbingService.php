<?php

namespace App\Services;

use App\Enums\StatusSkripsi;
use App\Exceptions\AturanBisnisException;
use App\Models\Dosen;
use App\Models\Skripsi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PembimbingService
{
    /**
     * Tetapkan pembimbing 1 dan 2 dalam satu transaksi.
     *
     * Pemeriksaan kuota dan penyimpanan harus satu kesatuan: baris skripsi dan
     * baris dosen dikunci (lockForUpdate) sehingga dua koordinator tidak dapat
     * mengisi slot kuota terakhir secara bersamaan. Urutan kunci selalu sama
     * (skripsi lalu dosen berurut id) agar tidak terjadi deadlock; bila tetap
     * terjadi, transaksi diulang otomatis hingga 3 kali.
     *
     * @throws AturanBisnisException
     */
    public function tetapkan(Skripsi $skripsi, int $pembimbing1, int $pembimbing2, string $tanggalSk): Skripsi
    {
        if ($pembimbing1 === $pembimbing2) {
            throw new AturanBisnisException('Pembimbing 1 dan pembimbing 2 harus dua dosen yang berbeda.');
        }

        return DB::transaction(function () use ($skripsi, $pembimbing1, $pembimbing2, $tanggalSk) {
            $terkunci = Skripsi::query()->whereKey($skripsi->id)->lockForUpdate()->firstOrFail();

            $dosen = Dosen::query()
                ->whereIn('id', [$pembimbing1, $pembimbing2])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($dosen->count() !== 2) {
                throw new AturanBisnisException('Dosen pembimbing tidak ditemukan.');
            }

            foreach ($dosen as $d) {
                $sudahDiSkripsiIni = $terkunci->dibimbingOleh($d);
                if (! $sudahDiSkripsiIni && $this->bebanAktif($d->id) >= $d->kuota) {
                    throw new AturanBisnisException("Kuota {$d->nama} sudah penuh.");
                }
            }

            $terkunci->update([
                'pembimbing_1_id' => $pembimbing1,
                'pembimbing_2_id' => $pembimbing2,
                'tanggal_sk' => $tanggalSk,
                'status' => $terkunci->status === StatusSkripsi::Diajukan
                    ? StatusSkripsi::Bimbingan
                    : $terkunci->status,
            ]);

            return $terkunci;
        }, attempts: 3);
    }

    /** Hitung langsung dari tabel (di dalam transaksi, setelah kunci diperoleh). */
    private function bebanAktif(int $dosenId): int
    {
        return Skripsi::query()
            ->where('status', '!=', StatusSkripsi::Lulus->value)
            ->where(fn (Builder $q) => $q
                ->where('pembimbing_1_id', $dosenId)
                ->orWhere('pembimbing_2_id', $dosenId))
            ->count();
    }
}
