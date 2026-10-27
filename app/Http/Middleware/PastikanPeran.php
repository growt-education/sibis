<?php

namespace App\Http\Middleware;

use App\Enums\PeranUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: middleware('peran:koordinator') atau middleware('peran:dosen,koordinator').
 */
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $diizinkan = array_filter(array_map(PeranUser::tryFrom(...), $peran));

        if (! $request->user() || ! $request->user()->adalah(...$diizinkan)) {
            abort(403, 'Anda tidak berhak membuka halaman ini.');
        }

        return $next($request);
    }
}
