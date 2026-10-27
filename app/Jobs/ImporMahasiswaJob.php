<?php

namespace App\Jobs;

use App\Enums\PeranUser;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Services\AkunService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * Impor massal mahasiswa dari CSV (kolom: nim, nama, angkatan, email, no_hp).
 *
 * Dirancang untuk ribuan baris: berkas dibaca sebagai aliran (hemat memori),
 * diproses per 500 baris dengan insert massal dalam satu transaksi, dan hash
 * kata sandi awal dihitung SEKALI (bcrypt per akun akan memakan puluhan menit).
 */
class ImporMahasiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    private const UKURAN_CHUNK = 500;

    public function __construct(
        public string $token,
        public string $path,
        public string $disk = 'local',
    ) {
    }

    public static function kunciCache(string $token): string
    {
        return 'impor-mahasiswa:'.$token;
    }

    public function handle(AkunService $akun): void
    {
        $ringkasan = ['status' => 'berjalan', 'total' => 0, 'berhasil' => 0, 'dilewati' => 0, 'galat' => []];
        $this->simpan($ringkasan);

        $hash = $akun->hashAwal();
        $aliran = Storage::disk($this->disk)->readStream($this->path);

        if (! is_resource($aliran)) {
            $this->gagal($ringkasan, 'Berkas tidak dapat dibaca.');

            return;
        }

        $baris = $this->bacaHeader($aliran);
        if ($baris === null) {
            fclose($aliran);
            $this->gagal($ringkasan, 'Header CSV harus memuat kolom: nim, nama, angkatan (email dan no_hp opsional).');

            return;
        }

        [$header, $pemisah] = $baris;
        $buffer = [];
        $nimDiBerkas = [];
        $nomor = 1;

        while (($kolom = fgetcsv($aliran, 0, $pemisah)) !== false) {
            $nomor++;
            if ($kolom === [null] || $kolom === []) {
                continue;
            }

            $ringkasan['total']++;
            $data = [];
            foreach ($header as $i => $nama) {
                $data[$nama] = isset($kolom[$i]) ? trim($kolom[$i]) : null;
            }

            $v = Validator::make($data, [
                'nim' => ['required', 'regex:/^[A-Za-z0-9]{5,20}$/'],
                'nama' => ['required', 'string', 'max:150'],
                'angkatan' => ['required', 'integer', 'between:2000,'.((int) date('Y') + 1)],
                'email' => ['nullable', 'email', 'max:255'],
                'no_hp' => ['nullable', 'string', 'max:20'],
            ]);

            if ($v->fails() || isset($nimDiBerkas[$data['nim']])) {
                $ringkasan['dilewati']++;
                if (count($ringkasan['galat']) < 20) {
                    $ringkasan['galat'][] = "Baris {$nomor}: ".($v->fails() ? $v->errors()->first() : 'NIM ganda di dalam berkas.');
                }

                continue;
            }

            $nimDiBerkas[$data['nim']] = true;
            $buffer[] = $data;

            if (count($buffer) >= self::UKURAN_CHUNK) {
                $this->prosesChunk($buffer, $hash, $akun, $ringkasan);
                $buffer = [];
                $this->simpan($ringkasan);
            }
        }

        fclose($aliran);

        if ($buffer !== []) {
            $this->prosesChunk($buffer, $hash, $akun, $ringkasan);
        }

        Storage::disk($this->disk)->delete($this->path);
        $ringkasan['status'] = 'selesai';
        $this->simpan($ringkasan);
    }

    /** @return array{0: list<string>, 1: string}|null */
    private function bacaHeader($aliran): ?array
    {
        $baris = fgets($aliran);
        if ($baris === false) {
            return null;
        }

        $baris = preg_replace('/^\xEF\xBB\xBF/', '', $baris);   // buang BOM Excel
        $pemisah = substr_count($baris, ';') > substr_count($baris, ',') ? ';' : ',';
        $header = array_map(fn ($h) => mb_strtolower(trim($h)), str_getcsv(trim($baris), $pemisah));

        foreach (['nim', 'nama', 'angkatan'] as $wajib) {
            if (! in_array($wajib, $header, true)) {
                return null;
            }
        }

        return [$header, $pemisah];
    }

    /**
     * @param  list<array<string, ?string>>  $baris
     * @param  array<string, mixed>  $ringkasan
     */
    private function prosesChunk(array $baris, string $hash, AkunService $akun, array &$ringkasan): void
    {
        $nimAda = Mahasiswa::query()->whereIn('nim', array_column($baris, 'nim'))->pluck('nim')->flip();

        $baris = array_values(array_filter($baris, fn ($r) => ! isset($nimAda[$r['nim']])));
        foreach ($baris as $i => $r) {
            $baris[$i]['email_login'] = $akun->emailMahasiswa($r['nim'], $r['email'] ?? null);
        }

        $emailAda = User::query()->whereIn('email', array_column($baris, 'email_login'))->pluck('email')->flip();
        $emailMhsAda = Mahasiswa::query()->whereNotNull('email')
            ->whereIn('email', array_column($baris, 'email_login'))->pluck('email')->flip();

        $baruSemua = array_values(array_filter(
            $baris,
            fn ($r) => ! isset($emailAda[$r['email_login']]) && ! isset($emailMhsAda[$r['email_login']])
        ));

        // dilewati: NIM sudah terdaftar + email sudah dipakai akun lain
        $ringkasan['dilewati'] += count($nimAda) + (count($baris) - count($baruSemua));

        if ($baruSemua === []) {
            return;
        }

        $sekarang = now();

        try {
            DB::transaction(function () use ($baruSemua, $hash, $sekarang) {
                User::query()->insert(array_map(fn ($r) => [
                    'name' => $r['nama'],
                    'email' => $r['email_login'],
                    'password' => $hash,
                    'peran' => PeranUser::Mahasiswa->value,
                    'wajib_ganti_password' => true,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ], $baruSemua));

                $idUser = User::query()->whereIn('email', array_column($baruSemua, 'email_login'))->pluck('id', 'email');

                Mahasiswa::query()->insert(array_map(fn ($r) => [
                    'user_id' => $idUser[$r['email_login']],
                    'nim' => $r['nim'],
                    'nama' => $r['nama'],
                    'angkatan' => (int) $r['angkatan'],
                    'no_hp' => $r['no_hp'] ?: null,
                    // simpan email hanya bila diisi di CSV (selain alamat cadangan NIM@domain)
                    'email' => ! empty($r['email']) ? $r['email_login'] : null,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ], $baruSemua));
            });

            $ringkasan['berhasil'] += count($baruSemua);
        } catch (Throwable $e) {
            Log::error('Impor mahasiswa: chunk gagal', ['pesan' => $e->getMessage()]);
            $ringkasan['dilewati'] += count($baruSemua);
            if (count($ringkasan['galat']) < 20) {
                $ringkasan['galat'][] = 'Satu kelompok '.count($baruSemua).' baris gagal disimpan (lihat log aplikasi).';
            }
        }
    }

    private function simpan(array $ringkasan): void
    {
        Cache::put(self::kunciCache($this->token), $ringkasan, now()->addHour());
    }

    private function gagal(array $ringkasan, string $pesan): void
    {
        $ringkasan['status'] = 'gagal';
        $ringkasan['galat'][] = $pesan;
        $this->simpan($ringkasan);
        Storage::disk($this->disk)->delete($this->path);
    }

    public function failed(Throwable $e): void
    {
        Log::error('Impor mahasiswa gagal', ['pesan' => $e->getMessage()]);
        Cache::put(self::kunciCache($this->token), [
            'status' => 'gagal', 'total' => 0, 'berhasil' => 0, 'dilewati' => 0,
            'galat' => ['Impor dihentikan karena kesalahan sistem.'],
        ], now()->addHour());
    }
}
