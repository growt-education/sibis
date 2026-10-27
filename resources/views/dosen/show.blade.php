@extends('layouts.app')
@section('judul', $dosen->nama)
@section('isi')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $dosen->nama }}</h1>
        <div class="text-muted">NIDN {{ $dosen->nidn }} · {{ $dosen->bidangMinat->nama }}</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="{{ route('dosen.edit', $dosen) }}">Ubah</a>
        <form method="POST" action="{{ route('dosen.reset-password', $dosen) }}" onsubmit="return confirm('Kembalikan kata sandi ke kata sandi awal?')">
            @csrf <button class="btn btn-outline-secondary">Reset kata sandi</button>
        </form>
        <form method="POST" action="{{ route('dosen.destroy', $dosen) }}" onsubmit="return confirm('Hapus dosen ini?')">
            @csrf @method('DELETE') <button class="btn btn-outline-danger">Hapus</button>
        </form>
    </div>
</div>
<div class="sibis-card p-3 mb-3">
    <div class="row">
        <div class="col-md-4"><div class="text-muted small">Email</div>{{ $dosen->email }}</div>
        <div class="col-md-4"><div class="text-muted small">Nomor HP</div>{{ $dosen->no_hp ?? '-' }}</div>
        <div class="col-md-4"><div class="text-muted small">Sisa kuota</div>{{ $sisaKuota }} dari {{ $dosen->kuota }}</div>
    </div>
</div>
<h2 class="h5">Mahasiswa bimbingan</h2>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Mahasiswa</th><th>Judul</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($skripsi as $s)
                <tr>
                    <td>{{ $s->mahasiswa->nama }} <span class="text-muted small">{{ $s->mahasiswa->nim }}</span></td>
                    <td><a href="{{ route('skripsi.show', $s) }}">{{ \Illuminate\Support\Str::limit($s->judul, 70) }}</a></td>
                    <td><span class="badge text-bg-{{ $s->status->warna() }}">{{ $s->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted py-4">Belum membimbing mahasiswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $skripsi->links() }}</div>
@endsection
