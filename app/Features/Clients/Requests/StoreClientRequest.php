<?php

namespace App\Features\Clients\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Clients\Models\Client;
use App\Features\Clients\Schemas\ClientFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(ClientFormSchema::create(), $this->all());
        $rules['user_id'] = ['required', 'exists:users,id'];
        return $rules;
    }
}
