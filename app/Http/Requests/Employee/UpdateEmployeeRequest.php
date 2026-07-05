<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:employees,code,' . $employeeId],
            'position_id' => ['required', 'exists:positions,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'join_date' => ['required', 'date'],
            'phone' => ['nullable', 'regex:/^08[0-9]{8,11}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'employee_type' => ['required', 'in:tetap,partime'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP harus diawali 08 dan berupa angka, contoh: 081234567890',
            'code.unique' => 'Kode/ID karyawan sudah dipakai karyawan lain',
        ];
    }
}