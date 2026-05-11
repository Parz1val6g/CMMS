<?php

namespace App\Features\Sectors\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Sectors\Models\Sector;
use App\Features\Sectors\SectorFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Sector::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(SectorFormSchema::create(), $this->all());
    }
}
