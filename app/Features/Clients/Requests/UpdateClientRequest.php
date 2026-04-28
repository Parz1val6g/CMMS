<?php

namespace App\Features\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client');

        return [
            'user_id' => ['sometimes', 'exists:users,id'],
            'nif' => ['sometimes', 'string', 'max:20', Rule::unique('clients', 'nif')->ignore($clientId)],
        ];
    }
}
