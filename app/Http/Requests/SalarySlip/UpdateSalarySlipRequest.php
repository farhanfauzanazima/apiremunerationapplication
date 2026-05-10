<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalarySlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'          => 'required|exists:salary_categories,id',
            'total_working_days'   => 'required|integer|min:0|max:31',
            'late_count'           => 'nullable|integer|min:0',
            'bonus'                => 'nullable|numeric|min:0',
            'additional_deduction' => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'        => 'Kategori gaji wajib dipilih.',
            'total_working_days.required' => 'Total hari kerja wajib diisi.',
            'total_working_days.max'      => 'Total hari kerja maksimal 31 hari.',
        ];
    }
}