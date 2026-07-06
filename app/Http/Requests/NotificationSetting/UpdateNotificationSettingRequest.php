<?php

namespace App\Http\Requests\NotificationSetting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'whatsapp_template' => ['required', 'string', 'max:1000'],
        ];
    }
}