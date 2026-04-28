<?php
namespace App\Features\WorkLogs\Requests;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Foundation\Http\FormRequest;
class StoreWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkLog::class);
    }
    public function rules(): array
    {
        return [
            'mini_task_id' => ['required', 'exists:mini_tasks,id'],
            'description' => ['required', 'string', 'max:250'],
            'started_at' => ['required', 'date'],
            'completed_at' => ['nullable', 'date', 'after:started_at'], // Must be after start time!

            'worker_ids' => ['nullable', 'array'],
            'worker_ids.*' => ['exists:workers,id'],

            // Materials used
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}