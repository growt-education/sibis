<?php

namespace App\Http\Requests;

use App\Models\Bimbingan;
use Illuminate\Foundation\Http\FormRequest;

class PeriksaBimbinganRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Bimbingan $bimbingan */
        $bimbingan = $this->route('bimbingan');

        return $this->user()?->can('periksa', $bimbingan) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:disetujui,revisi'],
            'catatan_dosen' => ['nullable', 'required_if:status,revisi', 'string', 'max:5000'],
        ];
    }
}
