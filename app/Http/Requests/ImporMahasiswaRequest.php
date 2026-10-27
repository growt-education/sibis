<?php

namespace App\Http\Requests;

use App\Models\Skripsi;
use Illuminate\Foundation\Http\FormRequest;

class ImporMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', Skripsi::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'berkas' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ];
    }
}
