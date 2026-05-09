<?php

namespace App\Http\Requests\PayrollPeriod;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_name' => 'required|string|max:50|unique:payroll_periods,period_name',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'notes'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'period_name.required'      => 'Nama periode wajib diisi.',
            'period_name.unique'        => 'Nama periode sudah ada.',
            'period_name.max'           => 'Nama periode maksimal 50 karakter.',
            'start_date.required'       => 'Tanggal mulai wajib diisi.',
            'start_date.date'           => 'Format tanggal mulai tidak valid.',
            'end_date.required'         => 'Tanggal akhir wajib diisi.',
            'end_date.date'             => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal'   => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ];
    }
}