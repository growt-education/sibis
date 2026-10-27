@extends('layouts.app')
@section('judul', 'Beranda koordinator')
@section('isi')
@php($perStatus = $statistik['perStatus'])
<h1 class="h4 mb-3">Ringkasan bimbingan skripsi</h1>
<div class="row g-3 mb-4">
    @foreach ([
        ['Mahasiswa terdaftar', $statistik['mahasiswa']],
        ['Dosen', $statistik['dosen']],
        ['Menunggu pembimbing', $perStatus['diajukan'] ?? 0],
        ['Sedang bimbingan', $perStatus['bimbingan'] ?? 0],
        ['Lulus', $perStatus['lulus'] ?? 0],
    ] as [$label, $angka])
        <div class="col-6 col-md">
            <div class="sibis-card p-3 h-100">
                <div class="sibis-stat">{{ number_format($angka, 0, ',', '.') }}</div>
                <div class="text-muted small">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h5 mb-0">Beban bimbingan dosen</h2>
    <a href="{{ route('skripsi.index', ['status' => 'diajukan']) }}" class="btn btn-sm btn-outline-primary">Skripsi menunggu pembimbing</a>
</div>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Dosen</th><th class="text-end">Aktif</th><th class="text-end">Kuota</th><th class="text-end">Sisa</th></tr></thead>
            <tbody>
            @forelse ($dosen as $d)
                <tr>
                    <td><a href="{{ route('dosen.show', $d) }}">{{ $d->nama }}</a></td>
                    <td class="text-end">{{ $d->jumlahBimbinganAktif() }}</td>
                    <td class="text-end">{{ $d->kuota }}</td>
                    <td class="text-end"><span class="badge text-bg-{{ $d->sisaKuota() === 0 ? 'danger' : 'success' }}">{{ $d->sisaKuota() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada dosen. <a href="{{ route('dosen.create') }}">Tambah dosen</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $dosen->links() }}</div>
@endsection
