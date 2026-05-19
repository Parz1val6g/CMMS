<?php

namespace App\Features\Entities;

use App\Core\Enums\EntityType;
use App\Core\Forms\Fields\SelectInput;
use App\Core\Forms\Fields\TextInput;
use App\Core\Forms\FormSchema;

class EntityFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.entities.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.entities.name'))
                    ->setRequired()
                    ->setRules('required|string|max:255')
            )
            ->field(
                SelectInput::make('entity_type')
                    ->setLabel(__('forms.entities.entity_type'))
                    ->setRequired()
                    ->setOptions(EntityType::options())
                    ->setRules('required|string')
            )
            ->field(
                TextInput::make('nif')
                    ->setLabel(__('forms.entities.nif'))
                    ->helperText(__('forms.entities.nif_helper'))
                    ->setRules('nullable|string|max:20|unique:entities,nif')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.entities.phone'))
                    ->setRules('nullable|string|max:30')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.entities.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.entities.name'))
                    ->setRules('sometimes|string|max:255')
            )
            ->field(
                SelectInput::make('entity_type')
                    ->setLabel(__('forms.entities.entity_type'))
                    ->setOptions(EntityType::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                TextInput::make('nif')
                    ->setLabel(__('forms.entities.nif'))
                    ->helperText(__('forms.entities.nif_helper'))
                    ->setRules('sometimes|nullable|string|max:20')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.entities.phone'))
                    ->setRules('sometimes|nullable|string|max:30')
            );
    }
}
