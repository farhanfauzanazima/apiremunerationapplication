<?php

namespace App\Http\Requests\SalaryCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalaryCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('salary_categories', 'category_name')
                    ->ignore($this->route('salary_category')),
            ],
            'base_salary'   => 'required|numeric|min:0',
            'allowance'     => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'late_penalty'  => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_name.required' => 'Nama kategori wajib diisi.',
            'category_name.unique'   => 'Nama kategori sudah ada.',
            'category_name.max'      => 'Nama kategori maksimal 50 karakter.',
            'base_salary.required'   => 'Gaji pokok wajib diisi.',
            'base_salary.numeric'    => 'Gaji pokok harus berupa angka.',
            'base_salary.min'        => 'Gaji pokok tidak boleh kurang dari 0.',
        ];
    }
}