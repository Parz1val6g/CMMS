<?php

namespace App\Features\WorkLogs\Requests;

use App\Core\Forms\FormValidator;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\WorkLogs\WorkLogFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkLog::class);
    }

    public function rules(): array
    {
        $rules = (new FormValidator())->fromSchema(WorkLogFormSchema::create(), $this->all());

        // Worker assignment
        $rules['worker_ids'] = ['nullable', 'array'];
        $rules['worker_ids.*'] = ['exists:workers,id'];

        // Materials used
        $rules['materials'] = ['nullable', 'array'];
        $rules['materials.*.material_id'] = ['required', 'exists:materials,id'];
        $rules['materials.*.quantity_used'] = ['required', 'numeric', 'min:0.01'];

        return $rules;
    }
}