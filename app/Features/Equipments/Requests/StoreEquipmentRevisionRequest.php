<?php

namespace App\Features\Equipments\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Equipments\EquipmentRevisionFormSchema;
use App\Features\Equipments\Models\EquipmentRevision;
use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', EquipmentRevision::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(EquipmentRevisionFormSchema::create(), $this->all());
    }
}
