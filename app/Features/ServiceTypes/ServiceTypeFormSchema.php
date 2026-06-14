<?php

namespace App\Features\ServiceTypes;

use App\Core\Cache\RefCache;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput};

class ServiceTypeFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.service_types.create_title'))
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.service_types.sector'))
                    ->setRequired()
                    ->setOptions(RefCache::sectors())
                    ->setRules('uuid|exists:sectors,id')
            )
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_types.name'))
                    ->setRequired()
                    ->setRules('string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_types.description'))
                    ->setRequired()
                    ->setRows(3)
                    ->setRules('string|max:250')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.service_types.edit_title'))
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.service_types.sector'))
                    ->setOptions(RefCache::sectors())
                    ->setRules('sometimes|uuid|exists:sectors,id')
            )
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.service_types.name'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_types.description'))
                    ->setRows(3)
                    ->setRules('string|max:250')
            );
    }
}
