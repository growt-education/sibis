@extends('layouts.app')
@section('judul', 'Skripsi')
@section('isi')
<h1 class="h4 mb-3">Skripsi</h1>
<form method="GET" action="{{ route('skripsi.index') }}" class="row g-2 mb-3">
    <div class="col-md-5"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul, nama, atau NIM" aria-label="Cari skripsi"></div>
    <div class="col-md-3">
        <select name="status" class="form-select" aria-label="Status">
            <option value="">Semua status</option>
            @foreach ($daftarStatus as $s)
                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="bidang" class="form-select" aria-label="Bidang minat">
            <option value="">Semua bidang minat</option>
            @foreach ($bidang as $id => $nama)
                <option value="{{ $id }}" @selected((int) request('bidang') === $id)>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-1"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>No</th><th>Judul</th><th>Mahasiswa</th><th>Pembimbing</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($skripsi as $s)
                <tr>
                    <td>{{ $skripsi->firstItem() + $loop->index }}</td>
                    <td><a href="{{ route('skripsi.show', $s) }}">{{ \Illuminate\Support\Str::limit($s->judul, 70) }}</a><div class="small text-muted">{{ $s->bidangMinat->nama }}</div></td>
                    <td>{{ $s->mahasiswa->nama }}<div class="small text-muted">{{ $s->mahasiswa->nim }}</div></td>
                    <td class="small">{{ $s->pembimbing1?->nama ?? '-' }}<br>{{ $s->pembimbing2?->nama ?? '-' }}</td>
                    <td><span class="badge text-bg-{{ $s->status->warna() }}">{{ $s->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">
                    Tidak ada skripsi yang cocok.
                    @if (auth()->user()->peran === \App\Enums\PeranUser::Mahasiswa)
                        <a href="{{ route('skripsi.create') }}">Ajukan judul</a>.
                    @endif
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $skripsi->links() }}</div>
<p class="text-muted small">Menampilkan {{ $skripsi->firstItem() ?? 0 }}–{{ $skripsi->lastItem() ?? 0 }} dari {{ number_format($skripsi->total(), 0, ',', '.') }} skripsi</p>
@endsection
