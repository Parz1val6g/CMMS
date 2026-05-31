<?php

namespace App\Features\Tasks\Requests;

use App\Core\Enums\TaskStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Forms\FormValidator;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\TaskFormSchema;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(TaskFormSchema::update(), $this->all());
        $rules['sector_id'] = ['sometimes', 'exists:sectors,id'];
        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');
            if (!$task instanceof Task) return;

            $lockedStatuses = [
                TaskStatus::AWAITING_APPROVAL->value,
                TaskStatus::COMPLETED->value,
                TaskStatus::CANCELLED->value,
            ];

            if (!in_array($task->status->value, $lockedStatuses, true)) return;

            if ($this->has('start_date') || $this->has('end_date')) {
                $validator->errors()->add(
                    'start_date',
                    __('validation.task.period_locked')
                );
            }
        });

        $validator->after(function ($validator) {
            $task = $this->route('task');
            if (!$task instanceof Task) return;

            $startDate = $this->input('start_date', $task->start_date?->format('Y-m-d'));
            $endDate = $this->input('end_date', $task->end_date?->format('Y-m-d'));

            if (!$startDate || !$endDate) return;

            $so = ServiceOrder::find($task->service_order_id);
            if (!$so || !$so->start_date || !$so->end_date) return;

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

        $validator->after(function ($validator) {
            $task = $this->route('task');
            if (!$task instanceof Task) return;

            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            // Only validate if at least one date is being changed
            if (!$startDate && !$endDate) return;

            $newStart = $startDate ?? $task->start_date?->format('Y-m-d');
            $newEnd = $endDate ?? $task->end_date?->format('Y-m-d');

            if (!$newStart || !$newEnd) return;

            $conflicting = $task->miniTasks()
                ->whereNotIn('status', [
                    MiniTaskStatus::COMPLETED->value,
                    MiniTaskStatus::CANCELLED->value,
                ])
                ->where(function ($query) use ($newStart, $newEnd) {
                    $query->where('start_date', '<', $newStart)
                        ->orWhere('end_date', '>', $newEnd);
                })
                ->count();

            if ($conflicting > 0) {
                $message = __('validation.task.dates_conflict_mini_tasks', [
                    'count' => $conflicting,
                ]);
                $validator->errors()->add('start_date', $message);
                $validator->errors()->add('end_date', $message);
            }
        });
    }
}
