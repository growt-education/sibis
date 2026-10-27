<?php

use App\Http\Controllers\Api\BimbinganApiController;
use App\Http\Controllers\Api\DosenApiController;
use App\Http\Controllers\Api\SkripsiApiController;
use App\Http\Controllers\Api\TokenApiController;
use Illuminate\Support\Facades\Route;

// Semua rute di sini otomatis berawalan /api dan dibatasi laju (limiter "api").

// Publik (tanpa data pribadi)
Route::get('/skripsi', [SkripsiApiController::class, 'index']);
Route::get('/dosen', [DosenApiController::class, 'index']);
Route::get('/bidang-minat/{bidangMinat}/skripsi', [SkripsiApiController::class, 'perBidangMinat']);

// Menukar email + kata sandi dengan token (berlaku 7 hari)
Route::post('/token', [TokenApiController::class, 'store'])->middleware('throttle:login');

// Perlu token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/token', [TokenApiController::class, 'destroy']);
    Route::get('/saya/bimbingan', [BimbinganApiController::class, 'saya']);
});
