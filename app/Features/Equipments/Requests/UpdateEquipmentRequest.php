<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\EquipmentFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('equipment'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentFormSchema::update(), $this->all());
    }
}
