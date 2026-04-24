<?php
namespace App\Features\ServiceOrders\Requests;
use App\Core\Enums\ServicesOrdersPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // For now, allow any authenticated user. Later, you can check roles here!
        return true;
    }
    public function rules(): array
    {
        return [
            'process' => ['required', 'string', 'max:250'],
            'client_id' => ['nullable', 'exists:clients,id'],
            // Manager ID is automatically taken from the logged-in user in the controller
            'location_id' => ['required', 'exists:locations,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            // Validate against the Enum we saw in your Core folder!
            'priority' => ['required', Rule::enum(ServicesOrdersPriority::class)],
            'execution_date' => ['nullable', 'date'],            
            'tasks' => ['required', 'array', 'min:1'], // Must assign at least 1 task
            'tasks.*.sector_id' => ['required', 'exists:sectors,id'],
            'tasks.*.name' => ['required', 'string', 'max:150'],
        ];
    }
}