<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function rules()
    {
        $serviceId = $this->route('service') ? $this->route('service')->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:500',
                Rule::unique('services', 'title')->ignore($serviceId),
            ],
            'icon' => 'nullable|file|image|max:2048',
            'price' => 'required|numeric|min:0',
            'short_service_detail' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'steps' => 'required|array|min:1',
            'steps.*.id' => 'nullable|exists:service_steps,id',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.fields' => 'required|array|min:1',

            'steps.*.fields.*.id' => 'nullable|exists:service_fields,id',
            'steps.*.fields.*.label' => 'required|string|max:255',
            'steps.*.fields.*.document_key' => 'required|string|max:255',
            'steps.*.fields.*.type' => 'required|in:text,number,email,date,radio,textarea,select,rich_text,effective_date',
            'steps.*.fields.*.placeholder' => 'nullable|string|max:255',
            'steps.*.fields.*.required' => 'nullable|boolean',
            'steps.*.fields.*.column' => 'nullable|integer|min:1|max:12',
            'steps.*.fields.*.options' => 'nullable|array',
        ];
    }
}
