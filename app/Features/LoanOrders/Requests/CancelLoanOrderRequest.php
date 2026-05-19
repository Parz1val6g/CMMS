<?php

namespace App\Features\LoanOrders\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelLoanOrderRequest extends FormRequest
{
    /**
     * Authorization is handled by the controller via Gate::authorize('cancel', ...).
     * This request only validates the optional body field.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notes_cancel' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
