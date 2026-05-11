<?php

namespace App\Features\MiniTasks\Requests;

use App\Core\Forms\FormValidator;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\MiniTasks\MiniTaskFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreMiniTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MiniTask::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(MiniTaskFormSchema::create(), $this->all());

        // Worker/Team array validation
        $rules['worker_ids'] = ['nullable', 'array'];
        $rules['worker_ids.*'] = ['exists:workers,id'];
        $rules['team_ids'] = ['nullable', 'array'];
        $rules['team_ids.*'] = ['exists:teams,id'];

        // Materials array: [{ material_id: "uuid", planned_quantity: 5.5 }]
        $rules['materials'] = ['nullable', 'array'];
        $rules['materials.*.material_id'] = ['required', 'exists:materials,id'];
        $rules['materials.*.planned_quantity'] = ['required', 'numeric', 'min:0.01'];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }
}