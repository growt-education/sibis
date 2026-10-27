<?php

namespace App\Console\Commands;

use App\Enums\PeranUser;
use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Database\Seeders\BidangMinatSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DataDemo extends Command
{
    protected $signature = 'sibis:demo {--dosen=12} {--mahasiswa=20} {--kuota=8}';

    protected $description = 'Isi data contoh (untuk pengembangan dan uji beban). Semua akun berkata sandi "password".';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Data demo tidak boleh dibuat di produksi.');

            return self::FAILURE;
        }

        $nDosen = max(2, (int) $this->option('dosen'));
        $nMhs = max(1, (int) $this->option('mahasiswa'));
        $kuota = max(1, (int) $this->option('kuota'));
        $hash = Hash::make('password');   // sekali saja; bcrypt per akun akan sangat lambat
        $sekarang = now();

        if (BidangMinat::count() === 0) {
            $this->call('db:seed', ['--class' => BidangMinatSeeder::class, '--force' => true]);
        }
        $bidangId = BidangMinat::pluck('id')->all();

        // ---- dosen ----
        DB::transaction(function () use ($nDosen, $kuota, $hash, $sekarang, $bidangId) {
            $baris = [];
            for ($i = 1; $i <= $nDosen; $i++) {
                $baris[] = ['name' => fake()->name().', M.Kom.', 'email' => "dosen{$i}@sibis.test"];
            }
            DB::table('users')->insert(array_map(fn ($b) => $b + [
                'password' => $hash, 'peran' => PeranUser::Dosen->value, 'wajib_ganti_password' => false,
                'created_at' => $sekarang, 'updated_at' => $sekarang,
            ], $baris));

            $idUser = DB::table('users')->whereIn('email', array_column($baris, 'email'))->pluck('id', 'email');
            DB::table('dosen')->insert(array_map(fn ($b, $i) => [
                'user_id' => $idUser[$b['email']],
                'nidn' => sprintf('00%08d', $i + 1),
                'nama' => $b['name'],
                'bidang_minat_id' => $bidangId[$i % count($bidangId)],
                'email' => $b['email'],
                'no_hp' => '08'.random_int(100000000, 999999999),
                'kuota' => $kuota,
                'created_at' => $sekarang, 'updated_at' => $sekarang,
            ], $baris, array_keys($baris)));
        });
        $dosenId = Dosen::orderBy('id')->pluck('id', 'id')->values()->all();
        $dosenBidang = Dosen::pluck('bidang_minat_id', 'id')->all();

        // ---- mahasiswa (per 500 baris) ----
        $this->output->progressStart($nMhs);
        foreach (array_chunk(range(1, $nMhs), 500) as $chunk) {
            DB::transaction(function () use ($chunk, $hash, $sekarang) {
                $users = $mhs = [];
                foreach ($chunk as $i) {
                    $angkatan = 2020 + ($i % 5);
                    $nim = sprintf('%d%06d', $angkatan, $i);
                    $email = "{$nim}@mhs.sibis.test";
                    $nama = fake()->name();
                    $users[] = [
                        'name' => $nama, 'email' => $email, 'password' => $hash,
                        'peran' => PeranUser::Mahasiswa->value, 'wajib_ganti_password' => false,
                        'created_at' => $sekarang, 'updated_at' => $sekarang,
                    ];
                    $mhs[$email] = ['nim' => $nim, 'nama' => $nama, 'angkatan' => $angkatan];
                }
                DB::table('users')->insert($users);
                $idUser = DB::table('users')->whereIn('email', array_keys($mhs))->pluck('id', 'email');
                DB::table('mahasiswa')->insert(array_map(fn ($email, $m) => $m + [
                    'user_id' => $idUser[$email], 'no_hp' => '08'.random_int(100000000, 999999999),
                    'created_at' => $sekarang, 'updated_at' => $sekarang,
                ], array_keys($mhs), $mhs));
            });
            $this->output->progressAdvance(count($chunk));
        }
        $this->output->progressFinish();

        // ---- skripsi: sebanyak yang muat dalam kuota ----
        // Setiap skripsi memakai 2 slot dosen, jadi maksimum = jumlah dosen x (kuota / 2), dibulatkan ke bawah.
        $maks = $nDosen * intdiv($kuota, 2);
        $jumlah = min($nMhs, $maks);
        $mahasiswaId = Mahasiswa::orderBy('id')->limit($jumlah)->pluck('id')->all();
        $geser = max(1, intdiv($nDosen, 2));
        $bab = ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V'];

        foreach (array_chunk($mahasiswaId, 500, true) as $chunk) {
            DB::transaction(function () use ($chunk, $dosenId, $nDosen, $geser, $dosenBidang, $bab, $sekarang) {
                $skripsi = [];
                foreach ($chunk as $j => $idMhs) {
                    $p1 = $dosenId[$j % $nDosen];
                    $p2 = $dosenId[($j + $geser) % $nDosen];
                    $skripsi[$idMhs] = [$p1, $p2];
                }
                DB::table('skripsi')->insert(array_map(fn ($idMhs, $p) => [
                    'mahasiswa_id' => $idMhs,
                    'bidang_minat_id' => $dosenBidang[$p[0]],
                    'judul' => 'Sistem '.fake()->sentence(6),
                    'ringkasan' => fake()->paragraph(),
                    'pembimbing_1_id' => $p[0], 'pembimbing_2_id' => $p[1],
                    'status' => 'bimbingan', 'tanggal_sk' => $sekarang->copy()->subMonths(4)->toDateString(),
                    'created_at' => $sekarang, 'updated_at' => $sekarang,
                ], array_keys($skripsi), $skripsi));

                $idSkripsi = DB::table('skripsi')->whereIn('mahasiswa_id', array_keys($skripsi))->pluck('id', 'mahasiswa_id');
                $bimbingan = [];
                foreach ($skripsi as $idMhs => $p) {
                    $n = random_int(0, 10);
                    for ($i = 1; $i <= $n; $i++) {
                        $bimbingan[] = [
                            'skripsi_id' => $idSkripsi[$idMhs], 'dosen_id' => $i % 2 ? $p[0] : $p[1],
                            'tanggal' => $sekarang->copy()->subWeeks(12 - min($i, 11))->toDateString(),
                            'bab' => $bab[min(intdiv($i - 1, 2), 4)],
                            'uraian' => 'Konsultasi '.fake()->sentence(),
                            'status' => $i <= $n - 1 ? 'disetujui' : 'menunggu',
                            'created_at' => $sekarang, 'updated_at' => $sekarang,
                        ];
                    }
                }
                foreach (array_chunk($bimbingan, 1000) as $b) {
                    DB::table('bimbingan')->insert($b);
                }
            });
        }

        $this->info("Selesai: {$nDosen} dosen, {$nMhs} mahasiswa, {$jumlah} skripsi.");
        $this->line('Contoh login (kata sandi: password): dosen1@sibis.test, 2020000005@mhs.sibis.test');

        return self::SUCCESS;
    }
}
