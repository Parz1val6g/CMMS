<?php

namespace App\Features\Settings\Requests;

use App\Shared\Models\AppSetting;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AppSetting::class);
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
