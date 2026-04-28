<?php

namespace App\Features\Materials\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'unit_id' => ['required', 'exists:units,id'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
