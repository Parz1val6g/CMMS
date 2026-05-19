<?php

namespace App\Features\Entities\Requests;

use App\Core\Enums\EntityType;
use App\Features\Entities\Models\Entity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Entity::class);
    }

    public function rules(): array
    {
        return [
            'user_id'     => ['sometimes', 'uuid', 'exists:users,id'],
            'entity_type' => ['required', 'string', Rule::in(array_column(EntityType::cases(), 'value'))],
            'nif'         => ['nullable', 'string', 'max:20', 'unique:entities,nif'],
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'location_id' => ['nullable', 'uuid', 'exists:parishes,id'],
        ];
    }
}
