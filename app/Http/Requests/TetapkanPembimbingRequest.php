<?php

namespace App\Http\Requests;

use App\Models\Skripsi;
use Illuminate\Foundation\Http\FormRequest;

class TetapkanPembimbingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', Skripsi::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'pembimbing_1_id' => ['required', 'integer', 'exists:dosen,id'],
            'pembimbing_2_id' => ['required', 'integer', 'exists:dosen,id', 'different:pembimbing_1_id'],
            'tanggal_sk' => ['required', 'date'],
        ];
    }
}
