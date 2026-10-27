<?php

namespace App\Http\Controllers;

use App\Exceptions\AturanBisnisException;
use App\Http\Requests\ImporMahasiswaRequest;
use App\Jobs\ImporMahasiswaJob;
use App\Services\AkunService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImporMahasiswaController extends Controller
{
    public function form(): View
    {
        return view('mahasiswa.impor');
    }

    public function store(ImporMahasiswaRequest $request, AkunService $akun): RedirectResponse
    {
        try {
            $akun->pastikanSiap();
        } catch (AturanBisnisException $e) {
            return back()->with('gagal', $e->getMessage());
        }

        $token = (string) Str::uuid();
        $path = $request->file('berkas')->storeAs('impor', $token.'.csv', 'local');

        Cache::put(ImporMahasiswaJob::kunciCache($token), [
            'status' => 'antre', 'total' => 0, 'berhasil' => 0, 'dilewati' => 0, 'galat' => [],
        ], now()->addHour());

        ImporMahasiswaJob::dispatch($token, $path, 'local');

        return redirect()->route('mahasiswa.impor.status', $token);
    }

    public function status(string $token): View
    {
        $hasil = Cache::get(ImporMahasiswaJob::kunciCache($token));
        abort_unless($hasil, 404, 'Hasil impor tidak ditemukan atau sudah kedaluwarsa.');

        return view('mahasiswa.impor-status', compact('hasil'));
    }
}
