<?php

namespace App\Features\Tasks\Requests;

use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormValidator;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\TaskFormSchema;
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
    }
}
