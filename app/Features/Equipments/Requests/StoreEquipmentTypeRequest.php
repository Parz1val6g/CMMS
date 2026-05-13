<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\EquipmentType;
use App\Features\Equipments\EquipmentTypeFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EquipmentType::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentTypeFormSchema::create(), $this->all());
    }
}
