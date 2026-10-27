<?php

namespace App\Http\Requests;

use App\Enums\PeranUser;
use Illuminate\Foundation\Http\FormRequest;

class AjukanSkripsiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya mahasiswa bertautan data mahasiswa; aturan "satu skripsi" dijawab
        // dengan pesan jelas di controller (dan dijaga unique di database).
        return $this->user()?->peran === PeranUser::Mahasiswa && $this->user()->mahasiswa !== null;
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'min:10', 'max:255'],
            'bidang_minat_id' => ['required', 'exists:bidang_minat,id'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
