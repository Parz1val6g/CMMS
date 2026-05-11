<?php

namespace App\Features\Tasks\Requests;

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
}
