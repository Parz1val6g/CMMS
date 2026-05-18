<?php

namespace App\Features\LoanOrders\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $loanOrder = $this->route('loanOrder');
        return $this->user()->can('update', $loanOrder);
    }

    public function rules(): array
    {
        return [
            'entity_id'       => ['sometimes', 'nullable', 'uuid', 'exists:entities,id'],
            'client_id'       => ['sometimes', 'nullable', 'uuid', 'exists:clients,id'],
            'manager_id'      => ['sometimes', 'uuid', 'exists:users,id'],
            'equipment_ids'   => ['sometimes', 'array', 'min:1'],
            'equipment_ids.*' => ['uuid', 'exists:equipments,id'],
            'equipments'                  => ['sometimes', 'array'],
            'equipments.*.equipment_id'   => ['required_with:equipments', 'uuid', 'exists:equipments,id'],
            'equipments.*.start_date'     => ['nullable', 'date'],
            'equipments.*.end_date'       => ['nullable', 'date', 'after_or_equal:equipments.*.start_date'],
            'equipments.*.needs_operator' => ['nullable', 'boolean'],
            'start_date'      => ['sometimes', 'date'],
            'end_date'        => ['sometimes', 'date', 'after_or_equal:start_date'],
            'needs_operator'  => ['sometimes', 'boolean'],
            'status'          => ['sometimes', 'string'],
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
