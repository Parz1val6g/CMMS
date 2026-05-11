<?php

namespace App\Features\Teams\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Teams\Models\Team;
use App\Features\Teams\TeamFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Team::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(TeamFormSchema::create(), $this->all());
    }
}
