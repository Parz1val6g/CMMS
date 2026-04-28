<?php

namespace App\Features\Sectors\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'head_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
