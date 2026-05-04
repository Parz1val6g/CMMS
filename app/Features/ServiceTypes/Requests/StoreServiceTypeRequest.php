<?php

namespace App\Features\ServiceTypes\Requests;

use App\Core\Forms\FormValidator;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\ServiceTypes\Schemas\ServiceTypeFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceType::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(ServiceTypeFormSchema::create(), $this->all());
    }
}
