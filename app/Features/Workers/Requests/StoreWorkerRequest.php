<?php

namespace App\Features\Workers\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Workers\Models\Worker;
use App\Features\Workers\Schemas\WorkerFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Worker::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(WorkerFormSchema::create(), $this->all());
        $rules['user_id'] = ['required', 'exists:users,id'];
        return $rules;
    }
}
