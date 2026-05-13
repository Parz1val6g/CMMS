<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\EquipmentType;
use App\Features\Equipments\EquipmentTypeFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('equipmentType'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentTypeFormSchema::update(), $this->all());
    }
}
