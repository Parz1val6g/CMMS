<?php

namespace App\Features\LoanOrders\Requests;

use App\Features\LoanOrders\Models\LoanOrder;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', LoanOrder::class);
    }

    public function rules(): array
    {
        return [
            'entity_id'       => ['required_without:client_id', 'nullable', 'uuid', 'exists:entities,id'],
            'client_id'       => ['required_without:entity_id', 'nullable', 'uuid', 'exists:clients,id'],
            'manager_id'      => ['required', 'uuid', 'exists:users,id'],
            'equipment_ids'   => ['required', 'array', 'min:1'],
            'equipment_ids.*' => ['uuid', 'exists:equipments,id'],
            'equipments'                  => ['nullable', 'array'],
            'equipments.*.equipment_id'   => ['required', 'uuid', 'exists:equipments,id'],
            'equipments.*.start_date'     => ['nullable', 'date'],
            'equipments.*.end_date'       => ['nullable', 'date', 'after_or_equal:equipments.*.start_date'],
            'equipments.*.needs_operator' => ['nullable', 'boolean'],
            'start_date'      => ['nullable', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'needs_operator'  => ['nullable', 'boolean'],
            'description'     => ['nullable', 'string', 'max:2000'],
            'parish_id'       => ['nullable', 'uuid', 'exists:parishes,id'],
            'street'          => ['nullable', 'string', 'max:255'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
