@extends('layouts.app')
@section('judul', 'Beranda dosen')
@section('isi')
<h1 class="h4 mb-1">Bimbingan menunggu pemeriksaan</h1>
<p class="text-muted">Sisa kuota Anda: <strong>{{ $dosen->sisaKuota() }}</strong> dari {{ $dosen->kuota }} mahasiswa.</p>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Mahasiswa</th><th>Judul</th><th>Tanggal</th><th>Bab</th><th></th></tr></thead>
            <tbody>
            @forelse ($menunggu as $b)
                <tr>
                    <td>{{ $b->skripsi->mahasiswa->nama }}<br><span class="text-muted small">{{ $b->skripsi->mahasiswa->nim }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit($b->skripsi->judul, 70) }}</td>
                    <td>{{ $b->tanggal->translatedFormat('d M Y') }}</td>
                    <td>{{ $b->bab }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-primary" href="{{ route('skripsi.show', $b->skripsi_id) }}">Periksa</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada catatan yang menunggu. Semua sudah diperiksa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $menunggu->links() }}</div>
@endsection
