<?php

namespace App\Features\Materials\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Materials\Models\Material;
use App\Features\Materials\Schemas\MaterialFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('material'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(MaterialFormSchema::update(), $this->all());
    }
}
