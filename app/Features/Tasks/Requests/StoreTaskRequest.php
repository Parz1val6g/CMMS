<?php

namespace App\Features\Tasks\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\TaskFormSchema;
use App\Features\ServiceOrders\Models\ServiceOrder;
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $serviceOrderId = $this->input('service_order_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            if (!$serviceOrderId || !$startDate || !$endDate) {
                return;
            }

            $so = ServiceOrder::find($serviceOrderId);
            if (!$so || !$so->start_date || !$so->end_date) {
                return;
            }

            $soStart = $so->start_date->format('Y-m-d');
            $soEnd = $so->end_date->format('Y-m-d');

            if ($startDate < $soStart) {
                $validator->errors()->add(
                    'start_date',
                    __('validation.task.start_date_before_service_order', [
                        'start' => $soStart,
                        'end' => $soEnd,
                    ])
                );
            }

            if ($endDate > $soEnd) {
                $validator->errors()->add(
                    'end_date',
                    __('validation.task.end_date_after_service_order', [
                        'start' => $soStart,
                        'end' => $soEnd,
                    ])
                );
            }
        });
    }
}
