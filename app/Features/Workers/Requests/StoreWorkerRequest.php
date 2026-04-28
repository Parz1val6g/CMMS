<?php

namespace App\Features\Workers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ];
    }
}
