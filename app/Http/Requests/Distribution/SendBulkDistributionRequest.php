<?php

namespace App\Http\Requests\Distribution;

use Illuminate\Foundation\Http\FormRequest;

class SendBulkDistributionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'channel' => ['required', 'in:email,whatsapp'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:tetap,partime'],
            'items.*.id' => ['required', 'integer'],
        ];
    }
}