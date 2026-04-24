<?php
namespace App\Features\MiniTasks\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreMiniTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'task_id' => ['required', 'exists:tasks,id'],
            'description' => ['required', 'string', 'max:250'],

            // XOR Logic: Must have one, cannot have both!
            'worker_id' => ['nullable', 'exists:workers,id', 'prohibits:team_id'],
            'team_id' => ['nullable', 'exists:teams,id', 'prohibits:worker_id'],

            // // Required without forces them to pick at least one
            // 'worker_id' => ['required_without:team_id'],

            // Optional Materials array: [{ material_id: "uuid", planned_quantity: 5.5 }]
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.planned_quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
    public function messages(): array
    {
        return [
            'worker_id.prohibits' => 'Cannot assign both a worker and a team.',
            'team_id.prohibits' => 'Cannot assign both a team and a worker.',
            'worker_id.required_without' => 'You must assign either a worker or a team.',
        ];
    }
}