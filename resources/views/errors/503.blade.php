@extends('layouts.app')
@section('judul', 'Sedang dalam pemeliharaan')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">503</div>
    <h1 class="h4 mt-2">Sedang dalam pemeliharaan</h1>
    <p class="text-muted">SIBIS akan kembali sebentar lagi.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
