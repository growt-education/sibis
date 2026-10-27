@extends('layouts.app')
@section('judul', $mahasiswa->nama)
@section('isi')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $mahasiswa->nama }}</h1>
        <div class="text-muted">NIM {{ $mahasiswa->nim }} · Angkatan {{ $mahasiswa->angkatan }}</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('mahasiswa.edit', $mahasiswa) }}">Ubah</a>
        <form method="POST" action="{{ route('mahasiswa.reset-password', $mahasiswa) }}" onsubmit="return confirm('Kembalikan kata sandi ke kata sandi awal?')">
            @csrf <button class="btn btn-outline-secondary">Reset kata sandi</button>
        </form>
        <form method="POST" action="{{ route('mahasiswa.destroy', $mahasiswa) }}" onsubmit="return confirm('Hapus mahasiswa ini?')">
            @csrf @method('DELETE') <button class="btn btn-outline-danger">Hapus</button>
        </form>
    </div>
</div>
<div class="sibis-card p-3">
    <div class="row">
        <div class="col-md-4"><div class="text-muted small">Email</div>{{ $mahasiswa->email ?? '-' }}</div>
        <div class="col-md-4"><div class="text-muted small">Nomor HP</div>{{ $mahasiswa->no_hp ?? '-' }}</div>
        <div class="col-md-4"><div class="text-muted small">Skripsi</div>
            @if ($mahasiswa->skripsi)
                <a href="{{ route('skripsi.show', $mahasiswa->skripsi) }}">{{ \Illuminate\Support\Str::limit($mahasiswa->skripsi->judul, 60) }}</a>
            @else
                Belum mengajukan judul
            @endif
        </div>
    </div>
</div>
@endsection
