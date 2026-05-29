<?php

namespace App\Features\ServiceOrders\Requests;

use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('serviceOrder'));
    }

    public function rules(): array
    {
        return [
            'process'             => ['sometimes', 'string', 'max:500'],
            'manager_id'         => ['sometimes', 'uuid', 'exists:users,id'],
            'client_id'          => ['sometimes', 'uuid', 'exists:clients,id'],
            'client_location_id' => ['nullable', 'uuid', 'exists:client_locations,id'],
            'sector_ids'         => ['sometimes', 'array', 'min:1'],
            'sector_ids.*'       => ['uuid', 'exists:sectors,id'],
            'start_date'         => ['sometimes', 'date'],
            'end_date'           => ['sometimes', 'date', 'after_or_equal:start_date'],
            'priority'           => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'parish_id'          => ['sometimes', 'uuid', 'exists:parishes,id'],
            'street'             => ['sometimes', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'service_type_id'    => ['nullable', 'uuid', 'exists:service_types,id'],
            'reference_point'    => ['nullable', 'string', 'max:255'],
            'postal_code'        => ['nullable', 'string', 'max:20'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
