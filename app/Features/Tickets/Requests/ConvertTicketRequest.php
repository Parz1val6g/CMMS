<?php

namespace App\Features\Tickets\Requests;

use App\Core\Enums\WorkflowType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('convert', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'workflow_type'   => ['required', Rule::enum(WorkflowType::class)],
            'manager_id'      => ['required', 'uuid', 'exists:users,id'],
            'client_id'       => ['required', 'uuid', 'exists:clients,id'],
            'priority'        => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'description'     => ['nullable', 'string', 'max:2000'],
            'service_type_id' => ['nullable', 'uuid', 'exists:service_types,id'],
            'sector_ids'      => ['nullable', 'array'],
            'sector_ids.*'    => ['uuid', 'exists:sectors,id'],
            'parish_id'       => ['nullable', 'uuid', 'exists:parishes,id'],
            'street'          => ['nullable', 'string', 'max:255'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
