<?php

namespace App\Models;

use App\Enums\PeranUser;
use App\Enums\StatusBimbingan;
use App\Enums\StatusSkripsi;
use App\Support\Pencarian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skripsi extends Model
{
    use HasFactory;

    protected $table = 'skripsi';

    protected $fillable = [
        'mahasiswa_id', 'bidang_minat_id', 'judul', 'ringkasan',
        'pembimbing_1_id', 'pembimbing_2_id', 'status', 'tanggal_sk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'status' => StatusSkripsi::class,
        ];
    }

    public static function minBimbingan(): int
    {
        return (int) config('sibis.min_bimbingan', 8);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function bidangMinat(): BelongsTo
    {
        return $this->belongsTo(BidangMinat::class);
    }

    public function pembimbing1(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_1_id');
    }

    public function pembimbing2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_2_id');
    }

    public function bimbingan(): HasMany
    {
        return $this->hasMany(Bimbingan::class);
    }

    public function punyaPembimbing(): bool
    {
        return $this->pembimbing_1_id !== null && $this->pembimbing_2_id !== null;
    }

    public function dibimbingOleh(?Dosen $dosen): bool
    {
        return $dosen !== null
            && in_array($dosen->id, [$this->pembimbing_1_id, $this->pembimbing_2_id], true);
    }

    /** Gunakan withCount('bimbingan as bimbingan_disetujui_count', ...) agar hemat query. */
    public function jumlahDisetujui(): int
    {
        if (array_key_exists('bimbingan_disetujui_count', $this->attributes)) {
            return (int) $this->attributes['bimbingan_disetujui_count'];
        }

        if ($this->relationLoaded('bimbingan')) {
            return $this->bimbingan
                ->filter(fn (Bimbingan $b) => $b->status === StatusBimbingan::Disetujui)
                ->count();
        }

        return $this->bimbingan()->where('status', StatusBimbingan::Disetujui->value)->count();
    }

    public function bolehSeminarHasil(): bool
    {
        return $this->jumlahDisetujui() >= self::minBimbingan();
    }

    /** Batasi daftar skripsi sesuai hak akses pengguna (lihat juga SkripsiPolicy::view). */
    public function scopeDapatDilihatOleh(Builder $query, User $user): Builder
    {
        return match ($user->peran) {
            PeranUser::Koordinator => $query,
            PeranUser::Dosen => $user->dosen
                ? $query->where(fn (Builder $q) => $q
                    ->where('skripsi.pembimbing_1_id', $user->dosen->id)
                    ->orWhere('skripsi.pembimbing_2_id', $user->dosen->id))
                : $query->whereRaw('1 = 0'),
            PeranUser::Mahasiswa => $user->mahasiswa
                ? $query->where('skripsi.mahasiswa_id', $user->mahasiswa->id)
                : $query->whereRaw('1 = 0'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /** Cari berdasarkan judul, nama mahasiswa, atau NIM. */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata) {
            Pencarian::kolom($q, ['skripsi.judul'], $kata);
            $q->orWhereHas('mahasiswa', fn (Builder $m) => Pencarian::kolom($m, ['mahasiswa.nama', 'mahasiswa.nim'], $kata));
        });
    }
}
