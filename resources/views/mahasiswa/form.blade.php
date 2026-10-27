@extends('layouts.app')
@section('judul', $mahasiswa->exists ? 'Ubah mahasiswa' : 'Tambah mahasiswa')
@section('isi')
<div class="row"><div class="col-lg-7">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">{{ $mahasiswa->exists ? 'Ubah data mahasiswa' : 'Tambah mahasiswa' }}</h1>
        <form method="POST" novalidate action="{{ $mahasiswa->exists ? route('mahasiswa.update', $mahasiswa) : route('mahasiswa.store') }}">
            @csrf
            @if ($mahasiswa->exists) @method('PUT') @endif
            <div class="row">
                <div class="col-md-5"><x-input name="nim" label="NIM" :value="$mahasiswa->nim" required /></div>
                <div class="col-md-7"><x-input name="nama" label="Nama lengkap" :value="$mahasiswa->nama" required /></div>
            </div>
            <div class="row">
                <div class="col-md-4"><x-input name="angkatan" label="Angkatan" type="number" :value="$mahasiswa->angkatan" required /></div>
                <div class="col-md-8"><x-input name="no_hp" label="Nomor HP" :value="$mahasiswa->no_hp" /></div>
            </div>
            <x-input name="email" label="Email" type="email" :value="$mahasiswa->email"
                     :help="'Dipakai untuk login. Bila dikosongkan, login memakai NIM@'.config('sibis.domain_email_mahasiswa').'.'" />
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ $mahasiswa->exists ? route('mahasiswa.show', $mahasiswa) : route('mahasiswa.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div></div>
@endsection
