<?php

namespace App\Http\Requests\SalarySlip;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalarySlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_id'            => 'required|exists:payroll_periods,id',
            'employee_id'          => 'required|exists:employees,id',
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
            'period_id.required'          => 'Periode penggajian wajib dipilih.',
            'period_id.exists'            => 'Periode penggajian tidak ditemukan.',
            'employee_id.required'        => 'Karyawan wajib dipilih.',
            'employee_id.exists'          => 'Karyawan tidak ditemukan.',
            'category_id.required'        => 'Kategori gaji wajib dipilih.',
            'category_id.exists'          => 'Kategori gaji tidak ditemukan.',
            'total_working_days.required' => 'Total hari kerja wajib diisi.',
            'total_working_days.integer'  => 'Total hari kerja harus berupa angka.',
            'total_working_days.min'      => 'Total hari kerja tidak boleh negatif.',
            'total_working_days.max'      => 'Total hari kerja maksimal 31 hari.',
            'late_count.integer'          => 'Jumlah keterlambatan harus berupa angka.',
            'bonus.numeric'               => 'Bonus harus berupa angka.',
            'additional_deduction.numeric'=> 'Potongan tambahan harus berupa angka.',
        ];
    }
}