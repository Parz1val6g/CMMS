<?php

namespace App\Features\ServiceOrderCategories\Requests;

use App\Core\Forms\FormValidator;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Features\ServiceOrderCategories\ServiceOrderCategoryFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceOrderCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('serviceOrderCategory'));
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(ServiceOrderCategoryFormSchema::update(), $this->all());
    }
}
