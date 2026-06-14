<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\EquipmentRevisionFormSchema;
use App\Features\Equipments\Models\EquipmentRevision;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('equipmentRevision'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentRevisionFormSchema::update(), $this->all());
    }
}
