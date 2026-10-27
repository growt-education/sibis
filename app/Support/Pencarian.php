<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian teks yang aman dan portabel (PostgreSQL, MySQL, SQLite):
 * - karakter khusus LIKE (% _) dari pengguna di-escape,
 * - memakai LOWER(kolom) LIKE agar bisa memakai indeks trigram di PostgreSQL.
 */
class Pencarian
{
    public static function pola(string $kata): string
    {
        $kata = mb_strtolower(trim($kata));

        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $kata).'%';
    }

    /**
     * Tambahkan kondisi "salah satu kolom mengandung kata" pada $query.
     * $kolom berasal dari kode (bukan input pengguna).
     *
     * @param  list<string>  $kolom
     */
    public static function kolom(Builder $query, array $kolom, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $query;
        }

        $pola = self::pola($kata);

        return $query->where(function (Builder $q) use ($kolom, $pola) {
            foreach ($kolom as $k) {
                $q->orWhereRaw("LOWER({$k}) LIKE ? ESCAPE '!'", [$pola]);
            }
        });
    }
}
