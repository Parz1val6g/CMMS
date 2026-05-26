<?php

namespace App\Features\MiniTasks\Requests;

use App\Core\Forms\FormValidator;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\MiniTasks\MiniTaskFormSchema;
use App\Features\Tasks\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreMiniTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->can('create', MiniTask::class)) {
            return true;
        }

        // Task manager can create mini-tasks for their own tasks
        $taskId = $this->input('task_id');
        if ($taskId) {
            $task = Task::find($taskId);
            return $task && $task->manager_id === $this->user()->id;
        }

        return false;
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(MiniTaskFormSchema::create(), $this->all());

        // Date fields
        $rules['start_date'] = ['required', 'date'];
        $rules['end_date']   = ['required', 'date', 'after_or_equal:start_date'];

        // Worker/Team array validation
        $rules['worker_ids'] = ['nullable', 'array'];
        $rules['worker_ids.*'] = ['exists:workers,id'];
        $rules['team_ids'] = ['nullable', 'array'];
        $rules['team_ids.*'] = ['exists:teams,id'];

        // Materials array: [{ material_id: "uuid", planned_quantity: 5.5 }]
        $rules['materials'] = ['nullable', 'array'];
        $rules['materials.*.material_id'] = ['required', 'exists:materials,id'];
        $rules['materials.*.planned_quantity'] = ['required', 'numeric', 'min:0.01'];

        // Simple material_ids (for multiselect, without planned_quantity)
        $rules['material_ids'] = ['nullable', 'array'];
        $rules['material_ids.*'] = ['exists:materials,id'];

        // Equipment IDs
        $rules['equipment_ids'] = ['nullable', 'array'];
        $rules['equipment_ids.*'] = ['exists:equipments,id'];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}