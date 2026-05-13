<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\CountingType;
use App\Features\Equipments\CountingTypeFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreCountingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CountingType::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(CountingTypeFormSchema::create(), $this->all());
    }
}
