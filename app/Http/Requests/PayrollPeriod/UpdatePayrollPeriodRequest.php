<?php

namespace App\Http\Requests\PayrollPeriod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('payroll_periods', 'period_name')
                    ->ignore($this->route('payroll_period')),
            ],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'nullable|in:open,closed',
            'notes'      => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'period_name.required'    => 'Nama periode wajib diisi.',
            'period_name.unique'      => 'Nama periode sudah ada.',
            'start_date.required'     => 'Tanggal mulai wajib diisi.',
            'end_date.required'       => 'Tanggal akhir wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'status.in'               => 'Status harus open atau closed.',
        ];
    }
}