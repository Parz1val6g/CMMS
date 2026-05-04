<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Schemas\EquipmentFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Equipment::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentFormSchema::create(), $this->all());
    }
}
