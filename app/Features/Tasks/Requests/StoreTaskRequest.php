<?php

namespace App\Features\Tasks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_order_id' => ['required', 'exists:service_orders,id'],
            'name' => ['required', 'string', 'max:150'],
            'sector_ids' => ['required', 'array', 'min:1'],
            'sector_ids.*' => ['exists:sectors,id'],
        ];
    }
}
