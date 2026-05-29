<?php

namespace App\Features\ServiceOrders\Requests;

use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceOrder::class);
    }

    public function rules(): array
    {
        return [
            'manager_id'          => ['required', 'uuid', 'exists:users,id'],
            'client_id'           => ['nullable', 'uuid', 'exists:clients,id'],
            'client_location_id'  => ['nullable', 'uuid', 'exists:client_locations,id'],
            'sector_ids'          => ['required', 'array', 'min:1'],
            'sector_ids.*'        => ['uuid', 'exists:sectors,id'],
            'priority'            => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'parish_id'           => ['required_without:client_location_id', 'uuid', 'exists:parishes,id'],
            'street'              => ['required_without:client_location_id', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'service_type_id'     => ['nullable', 'uuid', 'exists:service_types,id'],
            'reference_point'     => ['nullable', 'string', 'max:255'],
            'postal_code'         => ['nullable', 'string', 'max:20'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            'photo'               => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}