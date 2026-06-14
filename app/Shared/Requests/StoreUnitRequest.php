<?php

namespace App\Shared\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Shared\Models\Unit::class);
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:50'],
            'abbreviation' => ['required', 'string', 'max:10', 'unique:units,abbreviation'],
            'step'         => ['sometimes', 'numeric', 'min:0.01'],
        ];
    }
}
