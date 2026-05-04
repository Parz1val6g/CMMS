<?php

namespace App\Features\ServiceOrders\Requests;

use App\Core\Enums\WorkflowType;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Schemas\ServiceOrderFormSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Core\Forms\FormValidator;

class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceOrder::class);
    }

    public function rules(): array
    {
        $wt = $this->input('workflow_type');
        $isLoan = $wt === WorkflowType::LOAN->value;

        // Common rules from schema
        $rules = (new FormValidator())->fromSchema(ServiceOrderFormSchema::create(), $this->all());
        $rules['workflow_type'] = ['required', Rule::enum(WorkflowType::class)];

        if ($isLoan) {
            // LOAN: minimal required fields
            $rules['process']      = ['required', 'string', 'max:250'];
            $rules['client_id']    = ['required', 'uuid', 'exists:clients,id'];
            $rules['equipment_id'] = ['required', 'uuid', 'exists:equipments,id'];
            $rules['description']  = ['nullable', 'string', 'max:2000'];

            // Exclude location inline fields
            $rules['parish_id']    = ['prohibited'];
            $rules['street']       = ['prohibited'];
            $rules['photo']        = ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'];
        } else {
            // STANDARD: location fields required
            $rules['process']      = ['required', 'string', 'max:250'];
            $rules['client_id']    = ['required', 'uuid', 'exists:clients,id'];
            $rules['priority']     = ['required', Rule::in(['low', 'normal', 'high', 'urgent'])];
            $rules['parish_id']    = ['required', 'uuid', 'exists:parishes,id'];
            $rules['street']       = ['required', 'string', 'max:255'];
            $rules['photo']        = ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'];
            $rules['description']  = ['required', 'string', 'max:2000'];
            $rules['equipment_id'] = ['prohibited'];
        }

        return $rules;
    }
}