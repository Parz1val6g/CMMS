<?php

namespace App\Features\Materials\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'unit_id' => ['sometimes', 'exists:units,id'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
