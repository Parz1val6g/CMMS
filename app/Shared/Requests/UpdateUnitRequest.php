<?php

namespace App\Shared\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('unit'));
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:50'],
            'abbreviation' => ['sometimes', 'string', 'max:10', 'unique:units,abbreviation,' . $this->route('unit')->id],
            'step'         => ['sometimes', 'numeric', 'min:0.01'],
        ];
    }
}
