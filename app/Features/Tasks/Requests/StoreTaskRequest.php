<?php

namespace App\Features\Tasks\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\TaskFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(TaskFormSchema::create(), $this->all());
        $rules['service_order_id'] = ['required', 'exists:service_orders,id'];
        $rules['sector_id'] = ['required', 'exists:sectors,id'];
        return $rules;
    }
}
