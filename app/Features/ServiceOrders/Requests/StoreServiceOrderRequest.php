<?php

namespace App\Features\ServiceOrders\Requests;

use App\Core\Enums\WorkflowType;
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
        $isLoan = $this->input('workflow_type') === WorkflowType::LOAN->value;

        $common = [
            'workflow_type'       => ['required', Rule::enum(WorkflowType::class)],
            'manager_id'          => ['required', 'uuid', 'exists:users,id'],
            'client_location_id'  => ['nullable', 'uuid', 'exists:client_locations,id'],
            'description'         => ['nullable', 'string', 'max:2000'],
            'reference_point'     => ['nullable', 'string', 'max:255'],
            'postal_code'         => ['nullable', 'string', 'max:20'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
        ];

        if ($isLoan) {
            return array_merge($common, [
                'client_id'       => ['required', 'uuid', 'exists:clients,id'],
                'priority'        => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
                'equipment_ids'   => ['required', 'array', 'min:1'],
                'equipment_ids.*' => ['uuid', 'exists:equipments,id'],
                'photo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
                'parish_id'       => ['nullable', 'uuid', 'exists:parishes,id'],
                'street'          => ['nullable', 'string', 'max:255'],
                'sector_ids'      => ['prohibited'],
                'service_type_id' => ['prohibited'],
            ]);
        }

        return array_merge($common, [
            'client_id'       => ['required', 'uuid', 'exists:clients,id'],
            'priority'        => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'sector_ids'      => ['required', 'array', 'min:1'],
            'sector_ids.*'    => ['uuid', 'exists:sectors,id'],
            'parish_id'       => ['required', 'uuid', 'exists:parishes,id'],
            'street'          => ['required', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'uuid', 'exists:service_types,id'],
            'photo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'equipment_ids'   => ['prohibited'],
        ]);
    }
}