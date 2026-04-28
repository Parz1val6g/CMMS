<?php

namespace App\Features\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:50', 'unique:roles,name,' . $this->route('role')->id],
        ];
    }
}
