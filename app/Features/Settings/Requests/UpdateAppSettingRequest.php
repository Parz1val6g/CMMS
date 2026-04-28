<?php

namespace App\Features\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized via Policy
    }

    public function rules(): array
    {
        return [
            'value' => 'required|array',
        ];
    }
}
