<?php

namespace App\Http\Controllers;

use App\Http\Requests\GantiPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunController extends Controller
{
    public function formPassword(Request $request): View
    {
        return view('akun.password', ['wajib' => $request->user()->wajib_ganti_password]);
    }

    public function ubahPassword(GantiPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'password' => $request->validated('password'),   // di-hash oleh cast
            'wajib_ganti_password' => false,
        ])->save();

        // Sesi lain milik akun ini (bila ada) tidak lagi sah.
        $request->session()->regenerate();
        $user->tokens()->delete();

        return redirect()->route('beranda')->with('sukses', 'Kata sandi berhasil diganti.');
    }
}
