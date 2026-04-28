<?php

namespace App\Features\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via Policy
    }

    public function rules(): array
    {
        return [
            'key'     => 'required|string|max:50',
            'value'   => 'required|array',
            'section' => 'required|string|max:50',
        ];
    }
}
