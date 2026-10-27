<?php

namespace App\Http\Controllers;

use App\Enums\PeranUser;
use App\Exceptions\AturanBisnisException;
use App\Http\Requests\MahasiswaRequest;
use App\Models\Mahasiswa;
use App\Services\AkunService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $mahasiswa = Mahasiswa::query()
            ->with('skripsi:id,mahasiswa_id,status')
            ->cari($request->query('q'))
            ->when($request->integer('angkatan'), fn ($q, $a) => $q->where('mahasiswa.angkatan', $a))
            ->orderBy('mahasiswa.nim')
            ->paginate((int) config('sibis.per_halaman'))
            ->withQueryString();

        return view('mahasiswa.index', compact('mahasiswa'));
    }

    public function create(): View
    {
        return view('mahasiswa.form', ['mahasiswa' => new Mahasiswa(['angkatan' => date('Y')])]);
    }

    public function store(MahasiswaRequest $request, AkunService $akun): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $request, $akun) {
                $user = $akun->buat($data['nama'], $request->emailLogin(), PeranUser::Mahasiswa);
                Mahasiswa::create([...$data, 'user_id' => $user->id]);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->with('gagal', $e->getMessage());
        }

        return redirect()->route('mahasiswa.index')
            ->with('sukses', 'Mahasiswa ditambahkan beserta akun login. Kata sandi awal wajib diganti saat masuk pertama kali.');
    }

    public function show(Mahasiswa $mahasiswa): View
    {
        $mahasiswa->load('skripsi:id,mahasiswa_id,judul,status');

        return view('mahasiswa.show', compact('mahasiswa'));
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        return view('mahasiswa.form', compact('mahasiswa'));
    }

    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        DB::transaction(function () use ($request, $mahasiswa) {
            $mahasiswa->update($request->validated());
            $mahasiswa->user?->update(['name' => $mahasiswa->nama, 'email' => $request->emailLogin()]);
        });

        return redirect()->route('mahasiswa.show', $mahasiswa)->with('sukses', 'Data mahasiswa diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        if ($mahasiswa->skripsi()->exists()) {
            return back()->with('gagal', 'Mahasiswa sudah memiliki skripsi sehingga tidak dapat dihapus.');
        }

        DB::transaction(function () use ($mahasiswa) {
            $user = $mahasiswa->user;
            $mahasiswa->delete();
            $user?->delete();
        });

        return redirect()->route('mahasiswa.index')->with('sukses', 'Mahasiswa dihapus.');
    }

    public function resetPassword(Mahasiswa $mahasiswa, AkunService $akun): RedirectResponse
    {
        abort_unless($mahasiswa->user, 404);

        try {
            $akun->reset($mahasiswa->user);
        } catch (AturanBisnisException $e) {
            return back()->with('gagal', $e->getMessage());
        }

        return back()->with('sukses', 'Kata sandi dikembalikan ke kata sandi awal; mahasiswa wajib menggantinya saat masuk.');
    }
}
