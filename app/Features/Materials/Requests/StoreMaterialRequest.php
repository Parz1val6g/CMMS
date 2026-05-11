<?php

namespace App\Features\Materials\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Materials\Models\Material;
use App\Features\Materials\MaterialFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Material::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(MaterialFormSchema::create(), $this->all());
    }
}
