@extends('layouts.app')
@section('judul', 'Akses ditolak')
@section('isi')
<div class="text-center py-5">
    <div class="sibis-stat text-muted">403</div>
    <h1 class="h4 mt-2">Akses ditolak</h1>
    <p class="text-muted">Anda tidak berhak membuka halaman ini.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke beranda</a>
</div>
@endsection
