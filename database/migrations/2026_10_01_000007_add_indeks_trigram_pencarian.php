<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hanya PostgreSQL: indeks trigram membuat pencarian LIKE '%kata%' tetap cepat
 * ketika data membesar. Dimatikan lewat SIBIS_PG_TRIGRAM=false bila ekstensi
 * pg_trgm tidak boleh dipasang.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! config('sibis.trigram')) {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX IF NOT EXISTS skripsi_judul_trgm ON skripsi USING gin (LOWER(judul) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS mahasiswa_nama_trgm ON mahasiswa USING gin (LOWER(nama) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS mahasiswa_nim_trgm ON mahasiswa USING gin (LOWER(nim) gin_trgm_ops)');
        DB::statement('CREATE INDEX IF NOT EXISTS dosen_nama_trgm ON dosen USING gin (LOWER(nama) gin_trgm_ops)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS skripsi_judul_trgm, mahasiswa_nama_trgm, mahasiswa_nim_trgm, dosen_nama_trgm');
    }
};
