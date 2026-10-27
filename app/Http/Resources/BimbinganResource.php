<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Bimbingan */
class BimbinganResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->toDateString(),
            'bab' => $this->bab,
            'dosen' => $this->whenLoaded('dosen', fn () => $this->dosen->nama),
            'status' => $this->status->value,
            'catatan_dosen' => $this->catatan_dosen,
            'ada_draf' => $this->file_draf !== null,
        ];
    }
}
