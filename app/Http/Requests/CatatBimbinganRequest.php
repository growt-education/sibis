<?php

namespace App\Http\Requests;

use App\Models\Skripsi;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatatBimbinganRequest extends FormRequest
{
    private function skripsi(): Skripsi
    {
        /** @var Skripsi */
        return $this->route('skripsi');
    }

    public function authorize(): bool
    {
        return $this->user()?->can('catatBimbingan', $this->skripsi()) ?? false;
    }

    public function rules(): array
    {
        $skripsi = $this->skripsi();

        return [
            // hanya pembimbing 1 atau 2 dari skripsi ini
            'dosen_id' => $skripsi->punyaPembimbing()
                ? ['required', Rule::in([$skripsi->pembimbing_1_id, $skripsi->pembimbing_2_id])]
                : ['required'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'bab' => ['required', 'string', 'max:50'],
            'uraian' => ['required', 'string', 'max:5000'],
            'file_draf' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf',
                'max:'.config('sibis.maks_draf_kb')],
        ];
    }

    /** Aturan bisnis #4: bimbingan baru boleh dicatat setelah pembimbing ditetapkan. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->skripsi()->punyaPembimbing()) {
                $v->errors()->add('dosen_id', 'Pembimbing belum ditetapkan koordinator.');
            }
        });
    }
}
