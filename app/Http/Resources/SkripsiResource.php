<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk JSON publik. Sengaja TIDAK memuat nama atau NIM mahasiswa:
 * data pribadi hanya boleh keluar lewat endpoint yang memerlukan login.
 *
 * @mixin \App\Models\Skripsi
 */
class SkripsiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'bidang_minat' => $this->whenLoaded('bidangMinat', fn () => $this->bidangMinat->nama),
            'status' => $this->status->value,
            'tahun' => $this->created_at?->year,
        ];
    }
}
