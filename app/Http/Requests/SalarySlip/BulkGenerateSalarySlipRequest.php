<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class BulkGenerateSalarySlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_id'                        => 'required|exists:payroll_periods,id',
            'employees'                        => 'required|array|min:1',
            'employees.*.employee_id'          => 'required|exists:employees,id',
            'employees.*.category_id'          => 'required|exists:salary_categories,id',
            'employees.*.total_working_days'   => 'required|integer|min:0|max:31',
            'employees.*.late_count'           => 'nullable|integer|min:0',
            'employees.*.bonus'                => 'nullable|numeric|min:0',
            'employees.*.additional_deduction' => 'nullable|numeric|min:0',
            'employees.*.notes'                => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'period_id.required'                      => 'Periode penggajian wajib dipilih.',
            'employees.required'                      => 'Data karyawan wajib diisi.',
            'employees.min'                           => 'Minimal 1 karyawan harus diinput.',
            'employees.*.employee_id.required'        => 'ID karyawan wajib diisi.',
            'employees.*.category_id.required'        => 'Kategori gaji wajib dipilih.',
            'employees.*.total_working_days.required' => 'Total hari kerja wajib diisi.',
        ];
    }
}