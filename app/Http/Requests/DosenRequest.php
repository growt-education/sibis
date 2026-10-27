<?php

namespace App\Http\Requests;

use App\Models\Dosen;
use App\Models\Skripsi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', Skripsi::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        /** @var Dosen|null $dosen */
        $dosen = $this->route('dosen');

        return [
            'nidn' => ['required', 'string', 'max:20', 'alpha_num', Rule::unique('dosen', 'nidn')->ignore($dosen)],
            'nama' => ['required', 'string', 'max:150'],
            'bidang_minat_id' => ['required', 'exists:bidang_minat,id'],
            'email' => ['required', 'email:rfc', 'max:255',
                Rule::unique('dosen', 'email')->ignore($dosen),
                Rule::unique('users', 'email')->ignore($dosen?->user_id)],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'kuota' => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }
}
