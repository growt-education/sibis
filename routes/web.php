<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\BidangMinatController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\ImporMahasiswaController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\SkripsiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Boleh dibuka walau kata sandi awal belum diganti.
    Route::get('/akun/password', [AkunController::class, 'formPassword'])->name('akun.password');
    Route::put('/akun/password', [AkunController::class, 'ubahPassword'])->name('akun.password.ubah');

    Route::middleware('wajib.ganti')->group(function () {
        Route::get('/', [BerandaController::class, 'index'])->name('beranda');

        // Semua peran; isi daftar dan akses detail disaring oleh policy.
        Route::get('/skripsi', [SkripsiController::class, 'index'])->name('skripsi.index');
        Route::get('/bimbingan/{bimbingan}/draf', [BimbinganController::class, 'unduhDraf'])
            ->whereNumber('bimbingan')->middleware('throttle:60,1')->name('bimbingan.draf');

        Route::middleware('peran:mahasiswa')->group(function () {
            Route::get('/skripsi/ajukan', [SkripsiController::class, 'create'])->name('skripsi.create');
            Route::post('/skripsi', [SkripsiController::class, 'store'])->name('skripsi.store');
            Route::post('/skripsi/{skripsi}/bimbingan', [BimbinganController::class, 'store'])
                ->whereNumber('skripsi')->middleware('throttle:unggah')->name('bimbingan.store');
        });

        Route::get('/skripsi/{skripsi}', [SkripsiController::class, 'show'])
            ->whereNumber('skripsi')->name('skripsi.show');

        Route::middleware('peran:dosen')->group(function () {
            Route::patch('/bimbingan/{bimbingan}/periksa', [BimbinganController::class, 'periksa'])
                ->whereNumber('bimbingan')->name('bimbingan.periksa');
        });

        Route::middleware('peran:koordinator')->group(function () {
            Route::resource('bidang-minat', BidangMinatController::class)
                ->except('show')->parameters(['bidang-minat' => 'bidang_minat']);

            Route::resource('dosen', DosenController::class)->parameters(['dosen' => 'dosen']);
            Route::post('/dosen/{dosen}/reset-password', [DosenController::class, 'resetPassword'])
                ->name('dosen.reset-password');

            Route::get('/mahasiswa/impor', [ImporMahasiswaController::class, 'form'])->name('mahasiswa.impor');
            Route::post('/mahasiswa/impor', [ImporMahasiswaController::class, 'store'])->name('mahasiswa.impor.simpan');
            Route::get('/mahasiswa/impor/{token}', [ImporMahasiswaController::class, 'status'])
                ->whereUuid('token')->name('mahasiswa.impor.status');
            Route::resource('mahasiswa', MahasiswaController::class)->parameters(['mahasiswa' => 'mahasiswa']);
            Route::post('/mahasiswa/{mahasiswa}/reset-password', [MahasiswaController::class, 'resetPassword'])
                ->name('mahasiswa.reset-password');

            Route::patch('/skripsi/{skripsi}/pembimbing', [SkripsiController::class, 'tetapkanPembimbing'])
                ->whereNumber('skripsi')->name('skripsi.pembimbing');
            Route::patch('/skripsi/{skripsi}/status', [SkripsiController::class, 'ubahStatus'])
                ->whereNumber('skripsi')->name('skripsi.status');
        });
    });
});
