@extends('layouts.app')
@section('judul', 'Terlalu banyak permintaan')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">429</div>
    <h1 class="h4 mt-2">Terlalu banyak permintaan</h1>
    <p class="text-muted">Tunggu sebentar sebelum mencoba lagi.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
