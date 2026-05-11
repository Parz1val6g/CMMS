<?php

namespace App\Features\ServiceTypes;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput};

class ServiceTypeFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.service_types.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_types.name'))
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_types.description'))
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.service_types.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_types.name'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_types.description'))
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            );
    }
}
