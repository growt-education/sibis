@extends('layouts.app')
@section('judul', 'Impor mahasiswa')
@section('isi')
<div class="row"><div class="col-lg-7">
    <div class="sibis-card p-4">
        <h1 class="h4 mb-3">Impor mahasiswa dari CSV</h1>
        <p>Berkas CSV harus memiliki baris judul dengan kolom <code>nim</code>, <code>nama</code>, <code>angkatan</code>,
           dan opsional <code>email</code>, <code>no_hp</code>. Pemisah koma atau titik koma, maksimal 4 MB.</p>
        <p class="text-muted small">NIM yang sudah terdaftar dilewati. Setiap akun baru memakai kata sandi awal dan
           wajib menggantinya saat masuk pertama kali. Impor diproses di latar belakang.</p>
        <form method="POST" action="{{ route('mahasiswa.impor.simpan') }}" enctype="multipart/form-data" novalidate>
            @csrf
            <x-input name="berkas" label="Berkas CSV" type="file" accept=".csv,.txt" required />
            <button class="btn btn-primary">Mulai impor</button>
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-link">Batal</a>
        </form>
    </div>
</div></div>
@endsection
