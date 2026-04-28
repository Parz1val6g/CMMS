<?php

namespace App\Features\Sectors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'head_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
