@extends('layouts.app')
@section('judul', 'Terjadi kesalahan')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">500</div>
    <h1 class="h4 mt-2">Terjadi kesalahan</h1>
    <p class="text-muted">Kesalahan ini sudah dicatat. Coba lagi beberapa saat lagi.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
