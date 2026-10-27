<?php

namespace App\Models;

use App\Enums\StatusSkripsi;
use App\Support\Pencarian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $fillable = [
        'user_id', 'nidn', 'nama', 'bidang_minat_id', 'email', 'no_hp', 'kuota',
    ];

    public function bidangMinat(): BelongsTo
    {
        return $this->belongsTo(BidangMinat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skripsiPembimbing1(): HasMany
    {
        return $this->hasMany(Skripsi::class, 'pembimbing_1_id');
    }

    public function skripsiPembimbing2(): HasMany
    {
        return $this->hasMany(Skripsi::class, 'pembimbing_2_id');
    }

    public function bimbingan(): HasMany
    {
        return $this->hasMany(Bimbingan::class);
    }

    /**
     * Sertakan jumlah mahasiswa bimbingan aktif (kolom beban_aktif) lewat satu
     * subquery, sehingga daftar dosen dengan sisa kuota tidak memicu N+1 query.
     */
    public function scopeDenganBeban(Builder $query): Builder
    {
        return $query->addSelect([
            'beban_aktif' => Skripsi::query()
                ->selectRaw('count(*)')
                ->where('status', '!=', StatusSkripsi::Lulus->value)
                ->where(fn (Builder $q) => $q
                    ->whereColumn('pembimbing_1_id', 'dosen.id')
                    ->orWhereColumn('pembimbing_2_id', 'dosen.id')),
        ]);
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        return Pencarian::kolom($query, ['dosen.nama', 'dosen.nidn'], $kata);
    }

    /** Jumlah mahasiswa yang sedang dibimbing (sebagai pembimbing 1 atau 2). */
    public function jumlahBimbinganAktif(): int
    {
        if (array_key_exists('beban_aktif', $this->attributes)) {
            return (int) $this->attributes['beban_aktif'];
        }

        return Skripsi::query()
            ->where('status', '!=', StatusSkripsi::Lulus->value)
            ->where(fn (Builder $q) => $q
                ->where('pembimbing_1_id', $this->id)
                ->orWhere('pembimbing_2_id', $this->id))
            ->count();
    }

    public function sisaKuota(): int
    {
        return max(0, (int) $this->kuota - $this->jumlahBimbinganAktif());
    }
}
