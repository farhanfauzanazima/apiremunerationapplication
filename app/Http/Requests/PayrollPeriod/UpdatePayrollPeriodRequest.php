<?php

namespace App\Http\Requests\PayrollPeriod;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'digits:4'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $periodId = $this->route('payroll_period')->id;

            $exists = \App\Models\PayrollPeriod::where('month', $this->input('month'))
                ->where('year', $this->input('year'))
                ->where('id', '!=', $periodId)
                ->exists();

            if ($exists) {
                $validator->errors()->add('month', 'Sudah ada periode penggajian untuk bulan dan tahun ini.');
            }
        });
    }
}