<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\CountingType;
use App\Features\Equipments\CountingTypeFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCountingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('countingType'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(CountingTypeFormSchema::update(), $this->all());
    }
}
