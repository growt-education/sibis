<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DosenResource;
use App\Models\Dosen;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DosenApiController extends Controller
{
    public function index(): JsonResponse
    {
        $data = Cache::remember('api.dosen', (int) config('sibis.cache_detik'), function () {
            $dosen = Dosen::query()->denganBeban()->with('bidangMinat:id,nama')->orderBy('dosen.nama')->get();

            return DosenResource::collection($dosen)->resolve();
        });

        return response()->json(['data' => $data]);
    }
}
