<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:employees,code'],
            'position_id' => ['required', 'exists:positions,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'join_date' => ['required', 'date'],
            'phone' => ['nullable', 'regex:/^08[0-9]{8,11}$/', 'unique:employees,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email'],
            'bank_account_number' => ['nullable', 'string', 'max:50', 'unique:employees,bank_account_number'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'employee_type' => ['required', 'in:tetap,partime'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP harus diawali 08 dan berupa angka, contoh: 081234567890',
            'phone.unique' => 'Nomor HP ini sudah terdaftar pada karyawan lain',
            'code.unique' => 'Kode/ID karyawan sudah dipakai karyawan lain',
            'email.unique' => 'Email ini sudah terdaftar pada karyawan lain',
            'bank_account_number.unique' => 'Nomor rekening ini sudah terdaftar pada karyawan lain',
        ];
    }
}