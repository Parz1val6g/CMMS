<?php

namespace App\Features\Tasks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'sector_ids' => ['sometimes', 'array', 'min:1'],
            'sector_ids.*' => ['exists:sectors,id'],
        ];
    }
}
