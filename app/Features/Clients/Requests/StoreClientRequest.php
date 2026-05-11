<?php

namespace App\Features\Clients\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Clients\ClientFormSchema;
use App\Features\Clients\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(ClientFormSchema::create(), $this->all());

        // Locations array — at least one entry required (sede enforcement is in withValidator)
        $rules['locations']                    = ['required', 'array', 'min:1'];
        $rules['locations.*.name']             = ['required', 'string', 'max:100'];
        $rules['locations.*.is_primary']       = ['nullable', 'boolean'];
        $rules['locations.*.parish_id']        = ['nullable', 'uuid', 'exists:parishes,id'];
        $rules['locations.*.postal_code']      = ['nullable', 'string', 'max:20'];
        $rules['locations.*.street_address']   = ['nullable', 'string', 'max:255'];
        $rules['locations.*.landmark']         = ['nullable', 'string', 'max:255'];
        $rules['locations.*.latitude']         = ['nullable', 'numeric', 'between:-90,90'];
        $rules['locations.*.longitude']        = ['nullable', 'numeric', 'between:-180,180'];

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $locations = $this->input('locations', []);
            $hasSede   = collect($locations)->contains(fn($l) => !empty($l['is_primary']));

            if (!$hasSede) {
                $v->errors()->add('locations', __('validation.client.sede_required'));
            }
        });
    }
}
