@extends('layouts.app')
@section('judul', 'Ganti kata sandi')
@section('isi')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="sibis-card p-4">
            <h1 class="h4 mb-3">Ganti kata sandi</h1>
            @if ($wajib)
                <div class="alert alert-warning">Anda memakai kata sandi awal. Buat kata sandi baru sebelum melanjutkan.</div>
            @endif
            <form method="POST" action="{{ route('akun.password.ubah') }}" novalidate>
                @csrf @method('PUT')
                <x-input name="password_lama" label="Kata sandi saat ini" type="password" required autocomplete="current-password" />
                <x-input name="password" label="Kata sandi baru" type="password" required autocomplete="new-password"
                         help="Minimal 8 karakter, mengandung huruf dan angka." />
                <x-input name="password_confirmation" label="Ulangi kata sandi baru" type="password" required autocomplete="new-password" />
                <button class="btn btn-primary">Simpan kata sandi</button>
            </form>
        </div>
    </div>
</div>
@endsection
