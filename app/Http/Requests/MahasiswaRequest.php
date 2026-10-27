<?php

namespace App\Http\Requests;

use App\Models\Mahasiswa;
use App\Models\Skripsi;
use App\Services\AkunService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MahasiswaRequest extends FormRequest
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
        /** @var Mahasiswa|null $mhs */
        $mhs = $this->route('mahasiswa');

        return [
            'nim' => ['required', 'string', 'max:20', 'alpha_num', Rule::unique('mahasiswa', 'nim')->ignore($mhs)],
            'nama' => ['required', 'string', 'max:150'],
            'angkatan' => ['required', 'integer', 'between:2000,'.((int) date('Y') + 1)],
            'no_hp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:255',
                Rule::unique('mahasiswa', 'email')->ignore($mhs),
                Rule::unique('users', 'email')->ignore($mhs?->user_id)],
        ];
    }

    /** Email login akhir: email asli, atau NIM@domain bila dikosongkan. */
    public function emailLogin(): string
    {
        return app(AkunService::class)->emailMahasiswa($this->validated('nim'), $this->validated('email'));
    }
}
