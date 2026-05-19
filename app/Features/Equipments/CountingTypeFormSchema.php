<?php

namespace App\Features\Equipments;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, ToggleInput};

class CountingTypeFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('New Counting Type')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->setRules('required|string|max:200')
            )
            ->field(
                TextInput::make('value')
                    ->setLabel('Value')
                    ->setRules('nullable|string|max:50')
            )
            ->field(
                ToggleInput::make('active')
                    ->setLabel('Active')
                    ->setRules('boolean')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Edit Counting Type')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:200')
            )
            ->field(
                TextInput::make('value')
                    ->setLabel('Value')
                    ->setRules('nullable|string|max:50')
            )
            ->field(
                ToggleInput::make('active')
                    ->setLabel('Active')
                    ->setRules('boolean')
            );
    }
}
