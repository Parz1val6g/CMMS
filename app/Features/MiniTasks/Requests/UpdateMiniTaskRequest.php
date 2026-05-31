<?php

namespace App\Features\MiniTasks\Requests;

use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMiniTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('miniTask'));
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'max:250'],
            'start_date'  => ['sometimes', 'nullable', 'date'],
            'end_date'    => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $miniTask = $this->route('miniTask');
            if (!$miniTask instanceof MiniTask) return;

            if ($miniTask->status === MiniTaskStatus::COMPLETED->value) {
                $validator->errors()->add(
                    'status',
                    __('validation.task.mini_task_completed')
                );
                return;
            }

            $task = $miniTask->task;
            if (!$task || !$task->start_date || !$task->end_date) return;

            $taskStart = $task->start_date->format('Y-m-d');
            $taskEnd = $task->end_date->format('Y-m-d');
            $mtStart = $this->input('start_date', $miniTask->start_date?->format('Y-m-d'));
            $mtEnd = $this->input('end_date', $miniTask->end_date?->format('Y-m-d'));

            if ($mtStart && $mtStart < $taskStart) {
                $validator->errors()->add(
                    'start_date',
                    __('validation.task.mini_task_start_date_before_task', [
                        'start' => $taskStart,
                        'end' => $taskEnd,
                    ])
                );
            }

            if ($mtEnd && $mtEnd > $taskEnd) {
                $validator->errors()->add(
                    'end_date',
                    __('validation.task.mini_task_end_date_after_task', [
                        'start' => $taskStart,
                        'end' => $taskEnd,
                    ])
                );
            }
        });
    }
}
