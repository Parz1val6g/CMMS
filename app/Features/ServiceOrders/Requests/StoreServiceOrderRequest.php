<?php
namespace App\Features\ServiceOrders\Requests;
use App\Core\Enums\ServicesOrdersPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'process' => ['required', 'string', 'max:250'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'priority' => ['required', Rule::enum(ServicesOrdersPriority::class)],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            // Smart Location Group — created on-the-fly
            'parish_id' => ['required', 'exists:parishes,id'],
            'street' => ['required', 'string', 'max:255'],
            'reference_point' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}