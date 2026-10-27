@extends('layouts.app')
@section('judul', 'Mahasiswa')
@section('isi')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Mahasiswa</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('mahasiswa.impor') }}" class="btn btn-outline-primary">Impor dari CSV</a>
        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">Tambah mahasiswa</a>
    </div>
</div>
<form method="GET" action="{{ route('mahasiswa.index') }}" class="row g-2 mb-3">
    <div class="col-md-6"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau NIM" aria-label="Cari mahasiswa"></div>
    <div class="col-md-4"><input type="number" name="angkatan" value="{{ request('angkatan') }}" class="form-control" placeholder="Angkatan, mis. 2022" aria-label="Angkatan"></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Skripsi</th></tr></thead>
            <tbody>
            @forelse ($mahasiswa as $m)
                <tr>
                    <td>{{ $m->nim }}</td>
                    <td><a href="{{ route('mahasiswa.show', $m) }}">{{ $m->nama }}</a></td>
                    <td>{{ $m->angkatan }}</td>
                    <td>
                        @if ($m->skripsi)
                            <span class="badge text-bg-{{ $m->skripsi->status->warna() }}">{{ $m->skripsi->status->label() }}</span>
                        @else
                            <span class="text-muted">Belum mengajukan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada mahasiswa yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $mahasiswa->links() }}</div>
<p class="text-muted small">Menampilkan {{ $mahasiswa->firstItem() ?? 0 }}–{{ $mahasiswa->lastItem() ?? 0 }} dari {{ number_format($mahasiswa->total(), 0, ',', '.') }} mahasiswa</p>
@endsection
