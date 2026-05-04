<?php

namespace App\Features\Sectors\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\Schemas\SectorFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sector'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(SectorFormSchema::update(), $this->all());
    }
}
