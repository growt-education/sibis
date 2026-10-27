<?php

namespace App\Http\Controllers;

use App\Enums\PeranUser;
use App\Exceptions\AturanBisnisException;
use App\Http\Requests\DosenRequest;
use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\Skripsi;
use App\Services\AkunService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(Request $request): View
    {
        $dosen = Dosen::query()
            ->denganBeban()
            ->with('bidangMinat:id,nama')
            ->cari($request->query('q'))
            ->when($request->integer('bidang'), fn ($q, $id) => $q->where('dosen.bidang_minat_id', $id))
            ->orderBy('dosen.nama')
            ->paginate((int) config('sibis.per_halaman'))
            ->withQueryString();

        return view('dosen.index', ['dosen' => $dosen, 'bidang' => BidangMinat::opsi()]);
    }

    public function create(): View
    {
        return view('dosen.form', [
            'dosen' => new Dosen(['kuota' => config('sibis.kuota_bawaan')]),
            'bidang' => BidangMinat::opsi(),
        ]);
    }

    public function store(DosenRequest $request, AkunService $akun): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $akun) {
                $user = $akun->buat($data['nama'], $data['email'], PeranUser::Dosen);
                Dosen::create([...$data, 'user_id' => $user->id]);
            });
        } catch (AturanBisnisException $e) {
            return back()->withInput()->with('gagal', $e->getMessage());
        }

        return redirect()->route('dosen.index')
            ->with('sukses', 'Dosen ditambahkan beserta akun login. Kata sandi awal wajib diganti saat masuk pertama kali.');
    }

    public function show(Dosen $dosen): View
    {
        $dosen->load('bidangMinat:id,nama');

        $skripsi = Skripsi::query()
            ->with(['mahasiswa:id,nim,nama'])
            ->where(fn (Builder $q) => $q
                ->where('pembimbing_1_id', $dosen->id)
                ->orWhere('pembimbing_2_id', $dosen->id))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('dosen.show', [
            'dosen' => $dosen,
            'skripsi' => $skripsi,
            'sisaKuota' => $dosen->sisaKuota(),
        ]);
    }

    public function edit(Dosen $dosen): View
    {
        return view('dosen.form', ['dosen' => $dosen, 'bidang' => BidangMinat::opsi()]);
    }

    public function update(DosenRequest $request, Dosen $dosen): RedirectResponse
    {
        DB::transaction(function () use ($request, $dosen) {
            $dosen->update($request->validated());
            $dosen->user?->update(['name' => $dosen->nama, 'email' => Str::lower($dosen->email)]);
        });

        return redirect()->route('dosen.show', $dosen)->with('sukses', 'Data dosen diperbarui.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        if ($dosen->skripsiPembimbing1()->exists() || $dosen->skripsiPembimbing2()->exists() || $dosen->bimbingan()->exists()) {
            return back()->with('gagal', 'Dosen masih tercatat sebagai pembimbing atau memiliki catatan bimbingan.');
        }

        DB::transaction(function () use ($dosen) {
            $user = $dosen->user;
            $dosen->delete();
            $user?->delete();
        });

        return redirect()->route('dosen.index')->with('sukses', 'Dosen dihapus.');
    }

    public function resetPassword(Dosen $dosen, AkunService $akun): RedirectResponse
    {
        abort_unless($dosen->user, 404);

        try {
            $akun->reset($dosen->user);
        } catch (AturanBisnisException $e) {
            return back()->with('gagal', $e->getMessage());
        }

        return back()->with('sukses', 'Kata sandi dikembalikan ke kata sandi awal; dosen wajib menggantinya saat masuk.');
    }
}
