<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pengguna dengan kata sandi awal dialihkan ke halaman ganti kata sandi. */
class WajibGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->wajib_ganti_password) {
            return redirect()->route('akun.password')
                ->with('gagal', 'Ganti kata sandi awal Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
