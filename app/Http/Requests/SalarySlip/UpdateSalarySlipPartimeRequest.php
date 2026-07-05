<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalarySlipPartimeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hari_kerja' => ['nullable', 'integer', 'min:0'],
            'full' => ['nullable', 'integer', 'min:0'],
            'shift' => ['nullable', 'integer', 'min:0'],
            'reguler' => ['nullable', 'integer', 'min:0'],
            'sakit' => ['nullable', 'integer', 'min:0'],
            'off' => ['nullable', 'integer', 'min:0'],
            'tunjangan' => ['nullable', 'integer', 'min:0'],
            'bonus' => ['nullable', 'integer', 'min:0'],
        ];
    }
}