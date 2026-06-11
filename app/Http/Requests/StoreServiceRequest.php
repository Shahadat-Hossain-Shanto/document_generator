<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:500|unique:services,title',
            'icon' => 'nullable|file|image|max:2048',
            'price' => 'required|numeric|min:0',
            'short_service_detail' => 'required|string|max:500',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'steps' => 'required|array|min:1',

            'steps.*.title' => 'required|string|max:255',
            'steps.*.fields' => 'required|array|min:1',

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
