<?php

namespace App\Features\Locations\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Locations\Models\Location;
use App\Features\Locations\LocationFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('location'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(LocationFormSchema::update(), $this->all());
    }
}
