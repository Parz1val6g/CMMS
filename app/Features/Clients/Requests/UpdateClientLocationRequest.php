<?php

namespace App\Features\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate checked in controller
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:100'],
            'is_primary'     => ['nullable', 'boolean'],
            'parish_id'      => ['nullable', 'uuid', 'exists:parishes,id'],
            'postal_code'    => ['nullable', 'string', 'max:20'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'landmark'       => ['nullable', 'string', 'max:255'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
