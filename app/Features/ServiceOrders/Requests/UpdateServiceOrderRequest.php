<?php

namespace App\Features\ServiceOrders\Requests;

use App\Core\Enums\WorkflowType;
use App\Core\Forms\FormValidator;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Schemas\ServiceOrderFormSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service_order'));
    }

    public function rules(): array
    {
        $wt = $this->input('workflow_type');
        $isLoan = $wt === WorkflowType::LOAN->value;

        $rules = (new FormValidator())->fromSchema(ServiceOrderFormSchema::update(), $this->all());
        $rules['workflow_type'] = ['sometimes', Rule::enum(WorkflowType::class)];

        if ($isLoan) {
            // LOAN: location fields must not be sent
            $rules['parish_id']    = ['prohibited'];
            $rules['street']       = ['prohibited'];
            $rules['reference_point'] = ['prohibited'];
            $rules['postal_code']  = ['prohibited'];
            $rules['priority']     = ['prohibited'];
            $rules['service_type_id'] = ['prohibited'];
            $rules['location_id']  = ['prohibited'];
            $rules['equipment_id'] = ['sometimes', 'uuid', 'exists:equipments,id'];
        } else {
            // STANDARD: standard update rules
            $rules['location_id']  = ['sometimes', 'exists:locations,id'];
            $rules['priority']     = ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])];
            $rules['equipment_id'] = ['prohibited'];
        }

        return $rules;
    }
}
