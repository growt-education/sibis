<?php

namespace App\Http\Controllers;

use App\Http\Requests\BidangMinatRequest;
use App\Models\BidangMinat;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BidangMinatController extends Controller
{
    public function index(): View
    {
        $bidang = BidangMinat::query()
            ->withCount(['dosen', 'skripsi'])
            ->orderBy('nama')
            ->paginate((int) config('sibis.per_halaman'));

        return view('bidang-minat.index', compact('bidang'));
    }

    public function create(): View
    {
        return view('bidang-minat.form', ['bidang' => new BidangMinat]);
    }

    public function store(BidangMinatRequest $request): RedirectResponse
    {
        BidangMinat::create($request->validated());
        BidangMinat::lupakanOpsi();

        return redirect()->route('bidang-minat.index')->with('sukses', 'Bidang minat ditambahkan.');
    }

    public function edit(BidangMinat $bidang_minat): View
    {
        return view('bidang-minat.form', ['bidang' => $bidang_minat]);
    }

    public function update(BidangMinatRequest $request, BidangMinat $bidang_minat): RedirectResponse
    {
        $bidang_minat->update($request->validated());
        BidangMinat::lupakanOpsi();

        return redirect()->route('bidang-minat.index')->with('sukses', 'Bidang minat diperbarui.');
    }

    public function destroy(BidangMinat $bidang_minat): RedirectResponse
    {
        if ($bidang_minat->dosen()->exists() || $bidang_minat->skripsi()->exists()) {
            return back()->with('gagal', 'Bidang minat masih dipakai dosen atau skripsi sehingga tidak dapat dihapus.');
        }

        $bidang_minat->delete();
        BidangMinat::lupakanOpsi();

        return redirect()->route('bidang-minat.index')->with('sukses', 'Bidang minat dihapus.');
    }
}
