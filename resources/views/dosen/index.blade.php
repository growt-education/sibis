@extends('layouts.app')
@section('judul', 'Dosen')
@section('isi')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Dosen</h1>
    <a href="{{ route('dosen.create') }}" class="btn btn-primary">Tambah dosen</a>
</div>
<form method="GET" action="{{ route('dosen.index') }}" class="row g-2 mb-3">
    <div class="col-md-6"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau NIDN" aria-label="Cari dosen"></div>
    <div class="col-md-4">
        <select name="bidang" class="form-select" aria-label="Bidang minat">
            <option value="">Semua bidang minat</option>
            @foreach ($bidang as $id => $nama)
                <option value="{{ $id }}" @selected((int) request('bidang') === $id)>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Cari</button></div>
</form>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Nama</th><th>NIDN</th><th>Bidang minat</th><th class="text-end">Sisa kuota</th></tr></thead>
            <tbody>
            @forelse ($dosen as $d)
                <tr>
                    <td><a href="{{ route('dosen.show', $d) }}">{{ $d->nama }}</a></td>
                    <td>{{ $d->nidn }}</td>
                    <td>{{ $d->bidangMinat->nama }}</td>
                    <td class="text-end">{{ $d->sisaKuota() }} / {{ $d->kuota }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada dosen yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $dosen->links() }}</div>
<p class="text-muted small">Menampilkan {{ $dosen->firstItem() ?? 0 }}–{{ $dosen->lastItem() ?? 0 }} dari {{ $dosen->total() }} dosen</p>
@endsection
