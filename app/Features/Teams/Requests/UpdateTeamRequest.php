<?php

namespace App\Features\Teams\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sector_id' => ['sometimes', 'exists:sectors,id'],
            'name' => ['sometimes', 'string', 'max:150'],
        ];
    }
}
