<?php

namespace App\Http\Requests\SalarySetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalarySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transport_tetap' => ['required', 'integer', 'min:0'],
            'transport_partime' => ['required', 'integer', 'min:0'],
            'tenure_months_threshold' => ['required', 'integer', 'min:0'],
            'tenure_bonus_amount' => ['required', 'integer', 'min:0'],
            'disiplin_bonus_tetap' => ['required', 'integer', 'min:0'],
            'rate_full' => ['required', 'integer', 'min:0'],
            'rate_shift' => ['required', 'integer', 'min:0'],
            'rate_reguler' => ['required', 'integer', 'min:0'],
        ];
    }
}