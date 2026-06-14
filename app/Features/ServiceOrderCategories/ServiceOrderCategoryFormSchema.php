<?php

namespace App\Features\ServiceOrderCategories;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\TextInput;
use App\Core\Forms\Fields\TextAreaInput;

class ServiceOrderCategoryFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.service_order_categories.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_order_categories.name'))
                    ->setRequired()
                    ->setRules('string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_order_categories.description'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:250')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.service_order_categories.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_order_categories.name'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_order_categories.description'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:250')
            );
    }
}
