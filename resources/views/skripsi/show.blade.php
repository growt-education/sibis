@extends('layouts.app')
@section('judul', 'Kartu bimbingan')
@section('isi')
@php
    $user = auth()->user();
    $peran = $user->peran;
    $min = \App\Models\Skripsi::minBimbingan();
    $acc = $skripsi->jumlahDisetujui();
    $persen = min(100, (int) round($acc / max(1, $min) * 100));
    $koordinator = $peran === \App\Enums\PeranUser::Koordinator;
    $pemilik = $peran === \App\Enums\PeranUser::Mahasiswa;
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $skripsi->judul }}</h1>
        <div class="text-muted">
            {{ $skripsi->mahasiswa->nama }} ({{ $skripsi->mahasiswa->nim }}) · {{ $skripsi->bidangMinat->nama }}
        </div>
    </div>
    <span class="badge fs-6 text-bg-{{ $skripsi->status->warna() }}">{{ $skripsi->status->label() }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="sibis-card p-3 h-100">
            <div class="text-muted small">Pembimbing</div>
            <div>1. {{ $skripsi->pembimbing1?->nama ?? 'Belum ditetapkan' }}</div>
            <div>2. {{ $skripsi->pembimbing2?->nama ?? 'Belum ditetapkan' }}</div>
            @if ($skripsi->tanggal_sk) <div class="small text-muted mt-1">SK {{ $skripsi->tanggal_sk->translatedFormat('d F Y') }}</div> @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="sibis-card p-3 h-100">
            <div class="text-muted small">Bimbingan disetujui</div>
            <div class="mb-2"><span class="sibis-stat">{{ $acc }}</span> dari minimal {{ $min }}</div>
            <div class="progress" role="progressbar" aria-label="Bimbingan disetujui" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: {{ $persen }}%"></div>
            </div>
            @if ($skripsi->bolehSeminarHasil()) <div class="small text-success mt-2">Memenuhi syarat seminar hasil.</div> @endif
        </div>
    </div>
</div>

@if ($skripsi->ringkasan)
    <div class="sibis-card p-3 mb-4"><div class="text-muted small">Latar belakang</div>{{ $skripsi->ringkasan }}</div>
@endif

@if ($koordinator)
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="sibis-card p-3">
            <h2 class="h6">Tetapkan pembimbing</h2>
            <form method="POST" action="{{ route('skripsi.pembimbing', $skripsi) }}" novalidate>
                @csrf @method('PATCH')
                @php
                    $opsi = $daftarDosen->mapWithKeys(fn ($d) => [$d->id => $d->nama.' (sisa kuota '.$d->sisaKuota().')'])->all();
                @endphp
                <div class="row">
                    <div class="col-md-6"><x-select name="pembimbing_1_id" label="Pembimbing 1" :options="$opsi" :selected="$skripsi->pembimbing_1_id" required /></div>
                    <div class="col-md-6"><x-select name="pembimbing_2_id" label="Pembimbing 2" :options="$opsi" :selected="$skripsi->pembimbing_2_id" required /></div>
                </div>
                <x-input name="tanggal_sk" label="Tanggal SK" type="date" :value="$skripsi->tanggal_sk?->toDateString()" required />
                <button class="btn btn-primary">Tetapkan pembimbing</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="sibis-card p-3">
            <h2 class="h6">Ubah status</h2>
            <form method="POST" action="{{ route('skripsi.status', $skripsi) }}">
                @csrf @method('PATCH')
                <select name="status" class="form-select mb-2" aria-label="Status skripsi">
                    @foreach ($daftarStatus as $s)
                        <option value="{{ $s->value }}" @selected($skripsi->status === $s)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                @error('status') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                <button class="btn btn-outline-primary">Simpan status</button>
            </form>
        </div>
    </div>
</div>
@endif

<h2 class="h5">Kartu bimbingan</h2>
<div class="sibis-card mb-4">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>No</th><th>Tanggal</th><th>Dosen</th><th>Bab dan uraian</th><th>Catatan dosen</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($skripsi->bimbingan as $b)
                <tr class="{{ $b->status === \App\Enums\StatusBimbingan::Revisi ? 'table-warning' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-nowrap">{{ $b->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $b->dosen->nama }}</td>
                    <td>
                        <strong>{{ $b->bab }}</strong><br>{{ $b->uraian }}
                        @if ($b->file_draf)
                            <div><a class="small" href="{{ route('bimbingan.draf', $b) }}">Unduh draf PDF</a></div>
                        @endif
                    </td>
                    <td>{{ $b->catatan_dosen ?? '-' }}</td>
                    <td style="min-width: 15rem">
                        <span class="badge text-bg-{{ $b->status->warna() }}">{{ $b->status->label() }}</span>
                        @if ($b->status === \App\Enums\StatusBimbingan::Menunggu && $peran === \App\Enums\PeranUser::Dosen && $b->dosen_id === $user->dosen?->id)
                            <form method="POST" action="{{ route('bimbingan.periksa', $b) }}" class="mt-2">
                                @csrf @method('PATCH')
                                <textarea name="catatan_dosen" class="form-control form-control-sm mb-1" rows="2"
                                          placeholder="Catatan (wajib bila revisi)" aria-label="Catatan dosen"></textarea>
                                <button name="status" value="disetujui" class="btn btn-sm btn-success">Setujui</button>
                                <button name="status" value="revisi" class="btn btn-sm btn-warning">Revisi</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada catatan bimbingan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($pemilik && $skripsi->status !== \App\Enums\StatusSkripsi::Lulus)
<div class="sibis-card p-3">
    <h2 class="h6">Catat bimbingan baru</h2>
    @if (! $skripsi->punyaPembimbing())
        <p class="text-muted mb-0">Pembimbing belum ditetapkan koordinator. Anda dapat mencatat bimbingan setelah itu.</p>
    @else
        @php($pilihanDosen = [$skripsi->pembimbing_1_id => $skripsi->pembimbing1->nama, $skripsi->pembimbing_2_id => $skripsi->pembimbing2->nama])
        <form method="POST" action="{{ route('bimbingan.store', $skripsi) }}" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="row">
                <div class="col-md-4"><x-select name="dosen_id" label="Bertemu dengan" :options="$pilihanDosen" required /></div>
                <div class="col-md-4"><x-input name="tanggal" label="Tanggal" type="date" :value="now()->toDateString()" :max="now()->toDateString()" required /></div>
                <div class="col-md-4"><x-input name="bab" label="Bab yang dibahas" required placeholder="BAB III Metodologi" /></div>
            </div>
            <x-textarea name="uraian" label="Uraian konsultasi" rows="3" required />
            <x-input name="file_draf" label="Draf (PDF, maks. {{ round(config('sibis.maks_draf_kb') / 1024) }} MB)" type="file" accept="application/pdf,.pdf" />
            <button class="btn btn-primary">Kirim ke dosen</button>
        </form>
    @endif
</div>
@endif
@endsection
