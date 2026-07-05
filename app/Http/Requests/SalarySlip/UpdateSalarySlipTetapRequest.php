<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalarySlipTetapRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hari_kerja' => ['required', 'integer', 'min:0'],
            'alfa' => ['nullable', 'integer', 'min:0'],
            'izin' => ['nullable', 'integer', 'min:0'],
            'sakit' => ['nullable', 'integer', 'min:0'],
            'off' => ['nullable', 'integer', 'min:0'],
            'lembur' => ['nullable', 'integer', 'min:0'],
            'telat' => ['nullable', 'integer', 'min:0'],
            'harian' => ['nullable', 'integer', 'min:0'],
            'tunjangan_jabatan' => ['nullable', 'integer', 'min:0'],
            'tunjangan_bpjs' => ['nullable', 'integer', 'min:0'],
            'bonus_omset' => ['nullable', 'integer', 'min:0'],
            'bonus_kinerja' => ['nullable', 'integer', 'min:0'],
            'cashbond' => ['nullable', 'integer', 'min:0'],
            'tabungan' => ['nullable', 'integer', 'min:0'],
        ];
    }
}