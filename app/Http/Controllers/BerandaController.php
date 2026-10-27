<?php

namespace App\Http\Controllers;

use App\Enums\PeranUser;
use App\Enums\StatusBimbingan;
use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Skripsi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        return match ($user->peran) {
            PeranUser::Koordinator => $this->koordinator(),
            PeranUser::Dosen => $this->dosen($user->dosen),
            PeranUser::Mahasiswa => $this->mahasiswa($user->mahasiswa),
        };
    }

    private function koordinator(): View
    {
        // Angka ringkasan boleh terlambat beberapa detik; satu query agregat, di-cache.
        $statistik = Cache::remember('statistik.koordinator', (int) config('sibis.cache_detik'), function () {
            return [
                'mahasiswa' => Mahasiswa::count(),
                'dosen' => Dosen::count(),
                'perStatus' => Skripsi::query()
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->map(fn ($n) => (int) $n)
                    ->all(),
            ];
        });

        $dosen = Dosen::query()->denganBeban()
            ->orderBy('dosen.nama')
            ->paginate(15, pageName: 'dosen')
            ->withQueryString();

        return view('beranda.koordinator', compact('statistik', 'dosen'));
    }

    private function dosen(?Dosen $dosen): View
    {
        abort_unless($dosen, 403, 'Akun Anda belum dihubungkan ke data dosen.');

        $menunggu = Bimbingan::query()
            ->with(['skripsi:id,mahasiswa_id,judul', 'skripsi.mahasiswa:id,nim,nama'])
            ->where('dosen_id', $dosen->id)
            ->where('status', StatusBimbingan::Menunggu->value)
            ->orderBy('id')
            ->paginate((int) config('sibis.per_halaman'))
            ->withQueryString();

        return view('beranda.dosen', compact('dosen', 'menunggu'));
    }

    private function mahasiswa(?Mahasiswa $mahasiswa): RedirectResponse
    {
        abort_unless($mahasiswa, 403, 'Akun Anda belum dihubungkan ke data mahasiswa.');

        $skripsi = $mahasiswa->skripsi;

        return $skripsi
            ? redirect()->route('skripsi.show', $skripsi)
            : redirect()->route('skripsi.create');
    }
}
