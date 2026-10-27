@extends('layouts.app')
@section('judul', 'Halaman tidak ditemukan')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">404</div>
    <h1 class="h4 mt-2">Halaman tidak ditemukan</h1>
    <p class="text-muted">Alamat yang Anda buka tidak ada atau datanya sudah dihapus.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
