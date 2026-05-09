<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'   => 'required|exists:salary_categories,id',
            'full_name'     => 'required|string|max:100',
            'employee_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'employee_code')
                    ->ignore($this->route('employee')),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('employees', 'email')
                    ->ignore($this->route('employee')),
            ],
            'phone'     => 'required|string|max:15',
            'join_date' => 'nullable|date',
            'status'    => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori gaji wajib dipilih.',
            'category_id.exists'   => 'Kategori gaji tidak ditemukan.',
            'full_name.required'   => 'Nama lengkap wajib diisi.',
            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah digunakan.',
            'phone.required'       => 'Nomor telepon wajib diisi.',
            'status.in'            => 'Status harus active atau inactive.',
        ];
    }
}