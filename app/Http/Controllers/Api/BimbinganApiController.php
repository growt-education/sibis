<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BimbinganResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BimbinganApiController extends Controller
{
    /** Riwayat bimbingan milik mahasiswa yang sedang login (token Sanctum). */
    public function saya(Request $request): AnonymousResourceCollection
    {
        $skripsi = $request->user()->mahasiswa?->skripsi;
        abort_unless($skripsi, 404, 'Belum ada skripsi.');

        return BimbinganResource::collection(
            $skripsi->bimbingan()->with('dosen:id,nama')->orderBy('tanggal')->orderBy('id')->get()
        );
    }
}
