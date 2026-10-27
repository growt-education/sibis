# ERD dan kamus data SIBIS

Enam tabel sesuai Modul 5. Struktur kolom mengikuti Tabel 5.2–5.6 modul; perbedaannya hanya
penambahan **indeks**, **kolom pada `users`**, dan **batasan database** (ditandai ★).

```mermaid
erDiagram
    users ||--o| dosen : "user_id"
    users ||--o| mahasiswa : "user_id"
    bidang_minat ||--o{ dosen : "bidang_minat_id"
    bidang_minat ||--o{ skripsi : "bidang_minat_id"
    mahasiswa ||--o| skripsi : "mahasiswa_id (unik)"
    dosen ||--o{ skripsi : "pembimbing_1_id"
    dosen ||--o{ skripsi : "pembimbing_2_id"
    skripsi ||--o{ bimbingan : "skripsi_id"
    dosen ||--o{ bimbingan : "dosen_id"

    users { bigint id PK
            string name
            string email UK
            string password
            enum peran "koordinator|dosen|mahasiswa ★"
            bool wajib_ganti_password "★" }
    bidang_minat { bigint id PK
                   string nama UK
                   text deskripsi }
    dosen { bigint id PK
            bigint user_id FK,UK
            string nidn UK
            string nama
            bigint bidang_minat_id FK
            string email UK
            string no_hp
            tinyint kuota }
    mahasiswa { bigint id PK
                bigint user_id FK,UK
                string nim UK
                string nama
                smallint angkatan
                string no_hp
                string email UK }
    skripsi { bigint id PK
              bigint mahasiswa_id FK,UK
              bigint bidang_minat_id FK
              string judul
              text ringkasan
              bigint pembimbing_1_id FK
              bigint pembimbing_2_id FK
              enum status
              date tanggal_sk }
    bimbingan { bigint id PK
                bigint skripsi_id FK
                bigint dosen_id FK
                date tanggal
                string bab
                text uraian
                text catatan_dosen
                string file_draf
                enum status }
```

## Penambahan terhadap modul

| Tabel | Penambahan | Alasan |
|---|---|---|
| `users` | `peran`, `wajib_ganti_password`, indeks `peran` | peran akses; memaksa ganti kata sandi awal pada akun impor/reset |
| `skripsi` | ★ CHECK `pembimbing_1_id <> pembimbing_2_id` (PostgreSQL/MySQL) | aturan bisnis #2 dijaga database, bukan hanya form |
| `skripsi` | indeks `status`, `bidang_minat_id`, `(pembimbing_1_id,status)`, `(pembimbing_2_id,status)` | daftar/filter dan hitung kuota dosen |
| `bimbingan` | indeks `(skripsi_id,tanggal)`, `(skripsi_id,status)`, `(dosen_id,status)` | kartu bimbingan, hitung disetujui, antrean dosen |
| `dosen`, `mahasiswa` | indeks `nama`, `bidang_minat_id`/`angkatan` | daftar dan filter |
| semua FK | `restrictOnDelete` (kecuali `bimbingan.skripsi_id` cascade) | data akademik tidak hilang diam-diam |
| PostgreSQL | indeks GIN trigram pada `LOWER(judul/nama/nim)` | pencarian `LIKE '%kata%'` tetap cepat |

## Aturan bisnis dan penegaknya

| # | Aturan | Ditegakkan oleh |
|---|---|---|
| 1 | Satu mahasiswa satu skripsi | pemeriksaan controller + `UNIQUE(skripsi.mahasiswa_id)` |
| 2 | Pembimbing 1 ≠ 2 | Form Request `different` + CHECK database + service |
| 3 | Kuota dosen | `PembimbingService` (transaksi + `lockForUpdate`) |
| 4 | Bimbingan hanya setelah pembimbing ditetapkan, hanya dengan pembimbingnya | `CatatBimbinganRequest` + `SkripsiPolicy` |
| 5 | Bimbingan diperiksa dosen yang bersangkutan | `BimbinganPolicy::periksa` + update bersyarat `status='menunggu'` |
| 6 | Seminar hasil butuh ≥ 8 bimbingan disetujui | `StatusSkripsiService` (dihitung dalam transaksi) |
