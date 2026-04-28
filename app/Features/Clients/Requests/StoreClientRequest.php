<?php

namespace App\Features\Clients\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'nif' => ['required', 'string', 'max:20', 'unique:clients,nif'],
        ];
    }
}
