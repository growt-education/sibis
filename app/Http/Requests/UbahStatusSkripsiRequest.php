<?php

namespace App\Http\Requests;

use App\Enums\StatusSkripsi;
use App\Models\Skripsi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahStatusSkripsiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kelola', Skripsi::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusSkripsi::class)],
        ];
    }
}
