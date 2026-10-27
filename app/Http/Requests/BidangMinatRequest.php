<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BidangMinatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', \App\Models\Skripsi::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100',
                Rule::unique('bidang_minat', 'nama')->ignore($this->route('bidang_minat'))],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
