<?php

namespace App\Features\Settings\Requests;

use App\Shared\Models\AppSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', AppSetting::class);
    }

    public function rules(): array
    {
        return [
            'value' => 'required|array',
        ];
    }
}
