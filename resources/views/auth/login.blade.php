@extends('layouts.app')
@section('judul', 'Masuk')
@section('isi')
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="sibis-card p-4">
            <h1 class="h4 mb-1">Masuk ke SIBIS</h1>
            <p class="text-muted small mb-4">Sistem Informasi Bimbingan Skripsi, Prodi Ilmu Komputer.</p>
            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <x-input name="email" label="Email" type="email" required autofocus autocomplete="username" />
                <x-input name="password" label="Kata sandi" type="password" required autocomplete="current-password" />
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="ingat" id="ingat" value="1">
                    <label class="form-check-label" for="ingat">Ingat saya di perangkat ini</label>
                </div>
                <button class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</div>
@endsection
