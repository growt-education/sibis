@extends('layouts.app')
@section('judul', 'Ajukan judul skripsi')
@section('isi')
<div class="row"><div class="col-lg-7">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">Ajukan judul skripsi</h1>
        <form method="POST" action="{{ route('skripsi.store') }}" novalidate>
            @csrf
            <x-input name="judul" label="Judul skripsi" required />
            <x-select name="bidang_minat_id" label="Bidang minat" :options="$bidang" required />
            <x-textarea name="ringkasan" label="Latar belakang singkat" rows="5" />
            <button class="btn btn-primary">Ajukan judul</button>
        </form>
        <p class="text-muted small mt-3 mb-0">Cek kemiripan judul lewat <code>GET /api/skripsi?q=kata</code> sebelum mengajukan.</p>
    </div>
</div></div>
@endsection
