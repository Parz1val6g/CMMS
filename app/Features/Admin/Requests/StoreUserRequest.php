<?php

namespace App\Features\Admin\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Core\Enums\SystemStatus;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policies will secure the Controller
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:250'],
            'last_name' => ['required', 'string', 'max:250'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:14', 'unique:users,phone'],
            'status' => ['required', Rule::enum(SystemStatus::class)],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['exists:roles,id'],
        ];
    }
}
