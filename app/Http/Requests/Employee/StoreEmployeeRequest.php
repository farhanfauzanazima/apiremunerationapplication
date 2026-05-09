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
            'category_id'   => 'required|exists:salary_categories,id',
            'full_name'     => 'required|string|max:100',
            'employee_code' => 'nullable|string|max:20|unique:employees,employee_code',
            'email'         => 'required|email|unique:employees,email',
            'phone'         => 'required|string|max:15',
            'join_date'     => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'   => 'Kategori gaji wajib dipilih.',
            'category_id.exists'     => 'Kategori gaji tidak ditemukan.',
            'full_name.required'     => 'Nama lengkap wajib diisi.',
            'full_name.max'          => 'Nama lengkap maksimal 100 karakter.',
            'employee_code.unique'   => 'Kode karyawan sudah digunakan.',
            'email.required'         => 'Email wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'email.unique'           => 'Email sudah digunakan.',
            'phone.required'         => 'Nomor telepon wajib diisi.',
            'join_date.date'         => 'Format tanggal bergabung tidak valid.',
        ];
    }
}