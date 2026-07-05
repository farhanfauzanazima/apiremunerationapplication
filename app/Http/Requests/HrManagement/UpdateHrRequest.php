<?php

namespace App\Http\Requests\HrManagement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'has_all_branch_access' => ['required', 'boolean'],
            'branch_ids' => ['required_if:has_all_branch_access,false', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_ids.required_if' => 'Pilih minimal satu cabang jika tidak memberi akses semua cabang.',
        ];
    }
}