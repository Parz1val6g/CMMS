<?php

namespace App\Features\ServiceOrders\Requests;

use App\Core\Enums\WorkflowType;
use App\Core\Forms\FormValidator;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\ServiceOrderFormSchema;
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
        $wt = $this->input('workflow_type');
        $isLoan = $wt === WorkflowType::LOAN->value;

        $rules = (new FormValidator())->fromSchema(ServiceOrderFormSchema::update(), $this->all());
        $rules['workflow_type']      = ['sometimes', Rule::enum(WorkflowType::class)];
        $rules['manager_id']         = ['sometimes', 'uuid', 'exists:users,id'];
        $rules['client_location_id'] = ['nullable', 'uuid', 'exists:client_locations,id'];
        $rules['sector_ids']    = ['sometimes', 'array', 'min:1'];
        $rules['sector_ids.*']  = ['exists:sectors,id'];
        // MapInput generates a rule for key 'location', not for the hidden inputs
        // 'latitude'/'longitude' that the frontend actually submits — add them explicitly.
        $rules['latitude']  = ['nullable', 'numeric', 'between:-90,90'];
        $rules['longitude'] = ['nullable', 'numeric', 'between:-180,180'];

        if ($isLoan) {
            // LOAN: prohibited fields match StoreServiceOrderRequest loan rules
            $rules['service_type_id'] = ['prohibited'];
            $rules['sector_ids']      = ['prohibited'];
            $rules['location_id']     = ['prohibited'];
            // Allowed for loan (nullable, same as create)
            $rules['parish_id']       = ['nullable', 'uuid', 'exists:parishes,id'];
            $rules['street']          = ['nullable', 'string', 'max:255'];
            // Allowed for loan (same as create)
            $rules['reference_point'] = ['nullable', 'string', 'max:255'];
            $rules['postal_code']     = ['nullable', 'string', 'max:20'];
            $rules['priority']        = ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])];
            $rules['equipment_ids']   = ['sometimes', 'array', 'min:1'];
            $rules['equipment_ids.*'] = ['uuid', 'exists:equipments,id'];
        } else {
            // STANDARD: standard update rules
            $rules['location_id']  = ['sometimes', 'exists:locations,id'];
            $rules['priority']     = ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])];
            $rules['equipment_ids'] = ['prohibited'];
        }

        return $rules;
    }
}
