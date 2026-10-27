@extends('layouts.app')
@section('judul', $dosen->exists ? 'Ubah dosen' : 'Tambah dosen')
@section('isi')
<div class="row"><div class="col-lg-7">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">{{ $dosen->exists ? 'Ubah data dosen' : 'Tambah dosen' }}</h1>
        <form method="POST" novalidate action="{{ $dosen->exists ? route('dosen.update', $dosen) : route('dosen.store') }}">
            @csrf
            @if ($dosen->exists) @method('PUT') @endif
            <div class="row">
                <div class="col-md-5"><x-input name="nidn" label="NIDN" :value="$dosen->nidn" required /></div>
                <div class="col-md-7"><x-input name="nama" label="Nama lengkap dengan gelar" :value="$dosen->nama" required /></div>
            </div>
            <x-select name="bidang_minat_id" label="Bidang minat" :options="$bidang" :selected="$dosen->bidang_minat_id" required />
            <x-input name="email" label="Email" type="email" :value="$dosen->email" required help="Dipakai sebagai email login." />
            <div class="row">
                <div class="col-md-6"><x-input name="no_hp" label="Nomor HP" :value="$dosen->no_hp" /></div>
                <div class="col-md-6"><x-input name="kuota" label="Kuota mahasiswa bimbingan" type="number" min="0" max="50" :value="$dosen->kuota" required /></div>
            </div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ $dosen->exists ? route('dosen.show', $dosen) : route('dosen.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div></div>
@endsection
