<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class BidangMinat extends Model
{
    use HasFactory;

    protected $table = 'bidang_minat';

    protected $fillable = ['nama', 'deskripsi'];

    private const KUNCI_OPSI = 'bidang_minat.opsi';

    /** Daftar id => nama untuk dropdown (di-cache; dihapus setiap kali bidang minat berubah). */
    public static function opsi(): array
    {
        return Cache::remember(
            self::KUNCI_OPSI,
            (int) config('sibis.cache_detik'),
            fn () => static::query()->orderBy('nama')->pluck('nama', 'id')->all()
        );
    }

    public static function lupakanOpsi(): void
    {
        Cache::forget(self::KUNCI_OPSI);
    }

    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class);
    }

    public function skripsi(): HasMany
    {
        return $this->hasMany(Skripsi::class);
    }
}
