<?php

namespace App\Features\Entities\Requests;

use App\Core\Enums\EntityType;
use App\Features\Entities\Models\Entity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entity = $this->route('entity');
        return $this->user()->can('update', $entity);
    }

    public function rules(): array
    {
        $entityId = $this->route('entity')?->id;

        return [
            'entity_type' => ['sometimes', 'string', Rule::in(array_column(EntityType::cases(), 'value'))],
            'nif'         => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('entities', 'nif')->ignore($entityId)],
            'name'        => ['sometimes', 'string', 'max:255'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:30'],
            'location_id' => ['sometimes', 'nullable', 'uuid', 'exists:parishes,id'],
        ];
    }
}
