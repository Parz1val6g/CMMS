<?php

namespace App\Features\Clients\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Clients\Models\Client;
use App\Features\Clients\Schemas\ClientFormSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('client'));
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(ClientFormSchema::update(), $this->all());
        $rules['user_id'] = ['sometimes', 'exists:users,id'];
        $rules['nif'] = ['sometimes', 'string', 'max:20', Rule::unique('clients', 'nif')->ignore($this->route('client'))];
        return $rules;
    }
}
