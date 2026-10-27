@extends('layouts.app')
@section('judul', $bidang->exists ? 'Ubah bidang minat' : 'Tambah bidang minat')
@section('isi')
<div class="row"><div class="col-lg-6">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">{{ $bidang->exists ? 'Ubah bidang minat' : 'Tambah bidang minat' }}</h1>
        <form method="POST" novalidate
              action="{{ $bidang->exists ? route('bidang-minat.update', $bidang) : route('bidang-minat.store') }}">
            @csrf
            @if ($bidang->exists) @method('PUT') @endif
            <x-input name="nama" label="Nama" :value="$bidang->nama" required />
            <x-textarea name="deskripsi" label="Deskripsi" :value="$bidang->deskripsi" rows="3" />
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('bidang-minat.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div></div>
@endsection
