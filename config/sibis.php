<?php

return [
    // Syarat daftar seminar hasil: jumlah bimbingan yang disetujui.
    'min_bimbingan' => (int) env('SIBIS_MIN_BIMBINGAN', 8),

    // Kuota bawaan mahasiswa bimbingan aktif per dosen.
    'kuota_bawaan' => (int) env('SIBIS_KUOTA_BAWAAN', 8),

    // Kata sandi awal akun baru (dosen/mahasiswa). Pengguna WAJIB menggantinya
    // saat login pertama. Minimal 8 karakter, isi dengan nilai yang sulit ditebak.
    'password_awal' => env('SIBIS_PASSWORD_AWAL'),

    // Domain email cadangan untuk mahasiswa yang tidak punya email: NIM@domain.
    'domain_email_mahasiswa' => env('SIBIS_DOMAIN_MHS', 'mhs.sibis.test'),

    // Disk penyimpanan draf PDF (privat). Ganti ke 's3' bila memakai object storage.
    'disk_draf' => env('SIBIS_DISK_DRAF', 'local'),
    'maks_draf_kb' => (int) env('SIBIS_MAKS_DRAF_KB', 5120),

    // Lama cache (detik) untuk statistik beranda dan API publik. 0 = tanpa cache.
    'cache_detik' => (int) env('SIBIS_CACHE_DETIK', 60),

    'per_halaman' => (int) env('SIBIS_PER_HALAMAN', 15),

    // Kirim email notifikasi (antrean) saat bimbingan dicatat/diperiksa.
    'notifikasi_email' => (bool) env('SIBIS_NOTIFIKASI_EMAIL', true),

    // Buat indeks trigram (PostgreSQL pg_trgm) untuk pencarian LIKE '%kata%'.
    'trigram' => (bool) env('SIBIS_PG_TRIGRAM', true),
];
