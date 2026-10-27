<?php

namespace App\Http\Controllers;

use App\Enums\PeranUser;
use App\Enums\StatusSkripsi;
use App\Exceptions\AturanBisnisException;
use App\Http\Requests\AjukanSkripsiRequest;
use App\Http\Requests\TetapkanPembimbingRequest;
use App\Http\Requests\UbahStatusSkripsiRequest;
use App\Models\BidangMinat;
use App\Models\Dosen;
use App\Models\Skripsi;
use App\Services\PembimbingService;
use App\Services\StatusSkripsiService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SkripsiController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Skripsi::class);

        $status = StatusSkripsi::tryFrom((string) $request->query('status'));

        $skripsi = Skripsi::query()
            ->with(['mahasiswa:id,nim,nama', 'pembimbing1:id,nama', 'pembimbing2:id,nama', 'bidangMinat:id,nama'])
            ->dapatDilihatOleh($request->user())
            ->cari($request->query('q'))
            ->when($status, fn ($q) => $q->where('skripsi.status', $status->value))
            ->when($request->integer('bidang'), fn ($q, $id) => $q->where('skripsi.bidang_minat_id', $id))
            ->orderByDesc('skripsi.id')
            ->paginate((int) config('sibis.per_halaman'))
            ->withQueryString();

        return view('skripsi.index', [
            'skripsi' => $skripsi,
            'bidang' => BidangMinat::opsi(),
            'daftarStatus' => StatusSkripsi::cases(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->can('create', Skripsi::class)) {
            return redirect()->route('skripsi.index')->with('gagal', 'Anda sudah mengajukan judul skripsi.');
        }

        return view('skripsi.create', ['bidang' => BidangMinat::opsi()]);
    }

    public function store(AjukanSkripsiRequest $request): RedirectResponse
    {
        $mahasiswa = $request->user()->mahasiswa;

        // Aturan bisnis #1: satu mahasiswa hanya boleh memiliki satu skripsi.
        if ($mahasiswa->skripsi()->exists()) {
            return redirect()->route('skripsi.index')->with('gagal', 'Anda sudah mengajukan judul skripsi.');
        }

        try {
            $skripsi = $mahasiswa->skripsi()->create([
                ...$request->validated(),
                'status' => StatusSkripsi::Diajukan,
            ]);
        } catch (UniqueConstraintViolationException) {
            // dua klik bersamaan: batas unik di database menjaga satu mahasiswa satu skripsi
            return redirect()->route('skripsi.index')->with('gagal', 'Anda sudah mengajukan judul skripsi.');
        }

        return redirect()->route('skripsi.show', $skripsi)
            ->with('sukses', 'Judul diajukan. Tunggu penetapan pembimbing oleh koordinator.');
    }

    public function show(Request $request, Skripsi $skripsi): View
    {
        Gate::authorize('view', $skripsi);

        $skripsi->load([
            'mahasiswa', 'bidangMinat', 'pembimbing1', 'pembimbing2',
            'bimbingan' => fn ($q) => $q->with('dosen:id,nama')->orderBy('tanggal')->orderBy('id'),
        ]);

        $koordinator = $request->user()->peran === PeranUser::Koordinator;

        return view('skripsi.show', [
            'skripsi' => $skripsi,
            'daftarDosen' => $koordinator ? Dosen::query()->denganBeban()->orderBy('dosen.nama')->get() : collect(),
            'daftarStatus' => StatusSkripsi::cases(),
        ]);
    }

    public function tetapkanPembimbing(
        TetapkanPembimbingRequest $request,
        Skripsi $skripsi,
        PembimbingService $layanan,
    ): RedirectResponse {
        try {
            $layanan->tetapkan(
                $skripsi,
                (int) $request->validated('pembimbing_1_id'),
                (int) $request->validated('pembimbing_2_id'),
                $request->validated('tanggal_sk'),
            );
        } catch (AturanBisnisException $e) {
            return back()->withInput()->with('gagal', $e->getMessage());
        }

        return redirect()->route('skripsi.show', $skripsi)->with('sukses', 'Pembimbing berhasil ditetapkan.');
    }

    public function ubahStatus(
        UbahStatusSkripsiRequest $request,
        Skripsi $skripsi,
        StatusSkripsiService $layanan,
    ): RedirectResponse {
        try {
            $layanan->ubah($skripsi, StatusSkripsi::from($request->validated('status')));
        } catch (AturanBisnisException $e) {
            return back()->with('gagal', $e->getMessage());
        }

        return back()->with('sukses', 'Status skripsi diperbarui.');
    }
}
