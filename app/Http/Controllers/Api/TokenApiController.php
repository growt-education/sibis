<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TokenApiRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TokenApiController extends Controller
{
    public function store(TokenApiRequest $request): JsonResponse
    {
        $user = User::query()->where('email', Str::lower($request->validated('email')))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        abort_if($user->wajib_ganti_password, 403, 'Ganti kata sandi awal melalui situs web terlebih dahulu.');

        $token = $user->createToken($request->validated('nama_perangkat') ?? 'api', ['*'], now()->addDays(7));

        return response()->json([
            'token' => $token->plainTextToken,
            'tipe' => 'Bearer',
            'kedaluwarsa' => $token->accessToken->expires_at?->toIso8601String(),
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['pesan' => 'Token dicabut.']);
    }
}
