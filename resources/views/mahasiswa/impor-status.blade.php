@extends('layouts.app')
@section('judul', 'Hasil impor')
@push('kepala')
    @if (in_array($hasil['status'], ['antre', 'berjalan'], true))
        <meta http-equiv="refresh" content="3">
    @endif
@endpush
@section('isi')
<div class="row"><div class="col-lg-7">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">Hasil impor mahasiswa</h1>
        @switch($hasil['status'])
            @case('selesai') <div class="alert alert-success">Impor selesai.</div> @break
            @case('gagal') <div class="alert alert-danger">Impor gagal.</div> @break
            @default <div class="alert alert-info">Impor sedang diproses. Halaman ini diperbarui otomatis.</div>
        @endswitch
        <dl class="row mb-0">
            <dt class="col-sm-5">Baris dibaca</dt><dd class="col-sm-7">{{ $hasil['total'] }}</dd>
            <dt class="col-sm-5">Berhasil ditambahkan</dt><dd class="col-sm-7">{{ $hasil['berhasil'] }}</dd>
            <dt class="col-sm-5">Dilewati</dt><dd class="col-sm-7">{{ $hasil['dilewati'] }}</dd>
        </dl>
        @if (! empty($hasil['galat']))
            <h2 class="h6 mt-3">Catatan</h2>
            <ul class="small">
                @foreach ($hasil['galat'] as $g) <li>{{ $g }}</li> @endforeach
            </ul>
        @endif
        <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-primary mt-2">Ke daftar mahasiswa</a>
    </div>
</div></div>
@endsection
