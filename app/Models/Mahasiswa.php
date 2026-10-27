<?php

namespace App\Models;

use App\Support\Pencarian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id', 'nim', 'nama', 'angkatan', 'no_hp', 'email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skripsi(): HasOne
    {
        return $this->hasOne(Skripsi::class);
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        return Pencarian::kolom($query, ['mahasiswa.nama', 'mahasiswa.nim'], $kata);
    }
}
