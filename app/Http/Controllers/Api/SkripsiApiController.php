<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SkripsiResource;
use App\Models\BidangMinat;
use App\Models\Skripsi;
use App\Support\Pencarian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SkripsiApiController extends Controller
{
    /**
     * Daftar judul (publik) untuk memeriksa kemiripan judul sebelum mengajukan.
     * Pencarian hanya pada judul (bukan nama/NIM) dan hasilnya di-cache singkat
     * karena endpoint ini terbuka dan paling mungkin menerima lonjakan akses.
     */
    public function index(Request $request): JsonResponse
    {
        $perHalaman = min(50, max(1, (int) $request->query('per_page', 10)));
        $halaman = max(1, (int) $request->query('page', 1));
        $kata = trim((string) $request->query('q', ''));

        $kunci = sprintf('api.skripsi:%d:%d:%s', $perHalaman, $halaman, md5(mb_strtolower($kata)));

        $data = Cache::remember($kunci, (int) config('sibis.cache_detik'), function () use ($request, $perHalaman, $halaman, $kata) {
            $query = Skripsi::query()->with('bidangMinat:id,nama');
            Pencarian::kolom($query, ['skripsi.judul'], $kata);

            $paginator = $query->orderByDesc('skripsi.id')
                ->paginate($perHalaman, ['*'], 'page', $halaman)
                ->withQueryString();

            return SkripsiResource::collection($paginator)->response($request)->getData(true);
        });

        return response()->json($data);
    }

    public function perBidangMinat(Request $request, BidangMinat $bidangMinat): JsonResponse
    {
        $paginator = Skripsi::query()
            ->with('bidangMinat:id,nama')
            ->where('bidang_minat_id', $bidangMinat->id)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return SkripsiResource::collection($paginator)->response($request);
    }
}
