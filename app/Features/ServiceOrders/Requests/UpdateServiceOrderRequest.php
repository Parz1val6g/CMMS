<?php

namespace App\Features\ServiceOrders\Requests;

use App\Core\Enums\ServicesOrdersPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'process' => ['sometimes', 'string', 'max:250'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'location_id' => ['sometimes', 'exists:locations,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'priority' => ['sometimes', Rule::enum(ServicesOrdersPriority::class)],
            'execution_date' => ['nullable', 'date'],
        ];
    }
}
