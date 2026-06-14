<?php

namespace App\Features\ServiceOrderCategories\Requests;

use App\Core\Forms\FormValidator;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Features\ServiceOrderCategories\ServiceOrderCategoryFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceOrderCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ServiceOrderCategory::class);
    }

    public function rules(): array
    {
        return (new FormValidator())->fromSchema(ServiceOrderCategoryFormSchema::create(), $this->all());
    }
}
