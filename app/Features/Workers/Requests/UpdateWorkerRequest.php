<?php

namespace App\Features\Workers\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Workers\Models\Worker;
use App\Features\Workers\WorkerFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('worker'));
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(WorkerFormSchema::update(), $this->all());
        $rules['user_id'] = ['sometimes', 'exists:users,id'];
        return $rules;
    }
}
