<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlatformSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'company_name' => ['sometimes', 'string', 'max:255'],
            'support_mail' => ['sometimes', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.string' => 'Company name must be a string.',
            'company_name.max' => 'Company name cannot exceed 255 characters.',
            'support_email.email' => 'Support email must be a valid email address.',
            'support_email.max' => 'Support email cannot exceed 255 characters.',
        ];
    }
}