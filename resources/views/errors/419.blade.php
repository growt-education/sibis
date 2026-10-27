@extends('layouts.app')
@section('judul', 'Sesi kedaluwarsa')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">419</div>
    <h1 class="h4 mt-2">Sesi kedaluwarsa</h1>
    <p class="text-muted">Muat ulang halaman, lalu coba lagi.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
