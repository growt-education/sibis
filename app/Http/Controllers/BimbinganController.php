<?php

namespace App\Http\Controllers;

use App\Enums\StatusBimbingan;
use App\Http\Requests\CatatBimbinganRequest;
use App\Http\Requests\PeriksaBimbinganRequest;
use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Skripsi;
use App\Notifications\BimbinganBaru;
use App\Notifications\BimbinganDiperiksa;
use App\Support\Beritahu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BimbinganController extends Controller
{
    /** Mahasiswa mencatat pertemuan bimbingan (dengan draf PDF opsional). */
    public function store(CatatBimbinganRequest $request, Skripsi $skripsi): RedirectResponse
    {
        $data = $request->safe()->except('file_draf');
        $disk = config('sibis.disk_draf');
        $path = null;

        if ($request->hasFile('file_draf')) {
            // draf disimpan di disk PRIVAT dengan nama acak; hanya bisa diunduh lewat controller
            $path = $request->file('file_draf')->store("draf/{$skripsi->id}", $disk);
        }

        try {
            $bimbingan = $skripsi->bimbingan()->create([
                ...$data,
                'file_draf' => $path,
                'status' => StatusBimbingan::Menunggu,
            ]);
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk($disk)->delete($path);   // jangan tinggalkan berkas yatim
            }
            throw $e;
        }

        Beritahu::kirim(
            Dosen::query()->with('user')->find($bimbingan->dosen_id)?->user,
            new BimbinganBaru($bimbingan),
        );

        return back()->with('sukses', 'Catatan bimbingan terkirim ke dosen.');
    }

    /** Dosen memeriksa catatan bimbingan: disetujui atau revisi. */
    public function periksa(PeriksaBimbinganRequest $request, Bimbingan $bimbingan): RedirectResponse
    {
        $data = $request->validated();

        // Update bersyarat: dua klik bersamaan tidak memeriksa catatan yang sama dua kali.
        $berubah = Bimbingan::query()
            ->whereKey($bimbingan->id)
            ->where('status', StatusBimbingan::Menunggu->value)
            ->update([
                'status' => $data['status'],
                'catatan_dosen' => $data['catatan_dosen'] ?? null,
                'updated_at' => now(),
            ]);

        if ($berubah === 0) {
            return back()->with('gagal', 'Catatan bimbingan ini sudah diperiksa.');
        }

        $bimbingan->refresh()->load('skripsi.mahasiswa.user');

        Beritahu::kirim($bimbingan->skripsi->mahasiswa->user, new BimbinganDiperiksa($bimbingan));

        $pesan = $data['status'] === StatusBimbingan::Disetujui->value
            ? 'Bimbingan disetujui.' : 'Catatan revisi dikirim.';

        if ($bimbingan->skripsi->bolehSeminarHasil()) {
            $pesan .= ' Mahasiswa ini sudah memenuhi syarat seminar hasil.';
        }

        return back()->with('sukses', $pesan);
    }

    /** Unduh draf: hanya mahasiswa pemilik, kedua pembimbing, dan koordinator. */
    public function unduhDraf(Request $request, Bimbingan $bimbingan): StreamedResponse
    {
        Gate::authorize('unduhDraf', $bimbingan);

        $disk = Storage::disk(config('sibis.disk_draf'));
        abort_unless($bimbingan->file_draf && $disk->exists($bimbingan->file_draf), 404);

        $bimbingan->loadMissing('skripsi.mahasiswa');
        $nama = 'Draf-'.$bimbingan->skripsi->mahasiswa->nim.'-'.$bimbingan->tanggal->format('Ymd').'.pdf';

        return $disk->download($bimbingan->file_draf, $nama, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
