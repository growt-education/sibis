<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Dosen */
class DosenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'bidang_minat' => $this->whenLoaded('bidangMinat', fn () => $this->bidangMinat->nama),
            'kuota' => (int) $this->kuota,
            'sisa_kuota' => $this->sisaKuota(),
        ];
    }
}
