<?php

namespace App\Features\Tickets\Requests;

use App\Core\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'description'     => ['sometimes', 'string', 'max:5000'],
            'client_id'       => ['sometimes', 'nullable', 'uuid', 'exists:clients,id'],
            'service_type_id' => ['sometimes', 'nullable', 'uuid', 'exists:service_types,id'],
            'priority'        => ['sometimes', Rule::enum(TicketPriority::class)],
            'parish_id'       => ['sometimes', 'nullable', 'uuid', 'exists:parishes,id'],
            'street'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'reference_point' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postal_code'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'latitude'        => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
