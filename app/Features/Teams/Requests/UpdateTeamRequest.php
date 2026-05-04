<?php

namespace App\Features\Teams\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Teams\Models\Team;
use App\Features\Teams\Schemas\TeamFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('team'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(TeamFormSchema::update(), $this->all());
    }
}
