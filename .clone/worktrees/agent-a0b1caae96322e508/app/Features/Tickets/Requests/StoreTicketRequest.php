<?php

namespace App\Features\Tickets\Requests;

use App\Features\Tickets\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Ticket::class);
    }

    public function rules(): array
    {
        return [
            'client_id'         => ['nullable', 'uuid', 'exists:clients,id'],
            'service_type_id'   => ['nullable', 'uuid', 'exists:service_types,id'],
            'ticket_manager_id' => ['nullable', 'uuid', 'exists:users,id'],
            'priority'          => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'description'       => ['nullable', 'string', 'max:2000'],
            'parish_id'         => ['nullable', 'uuid', 'exists:parishes,id'],
            'street'            => ['nullable', 'string', 'max:255'],
            'reference_point'   => ['nullable', 'string', 'max:255'],
            'postal_code'       => ['nullable', 'string', 'max:20'],
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
