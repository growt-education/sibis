@extends('layouts.app')
@section('judul', 'Bidang minat')
@section('isi')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bidang minat</h1>
    <a href="{{ route('bidang-minat.create') }}" class="btn btn-primary">Tambah bidang minat</a>
</div>
<div class="sibis-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Nama</th><th class="text-end">Dosen</th><th class="text-end">Skripsi</th><th></th></tr></thead>
            <tbody>
            @forelse ($bidang as $b)
                <tr>
                    <td>{{ $b->nama }}</td>
                    <td class="text-end">{{ $b->dosen_count }}</td>
                    <td class="text-end">{{ $b->skripsi_count }}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('bidang-minat.edit', $b) }}">Ubah</a>
                        <form class="d-inline" method="POST" action="{{ route('bidang-minat.destroy', $b) }}"
                              onsubmit="return confirm('Hapus bidang minat ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada bidang minat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $bidang->links() }}</div>
@endsection
