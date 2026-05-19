<?php

namespace App\Features\Equipments;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, ToggleInput, TextAreaInput};

class EquipmentTypeFormSchema
{
    private static function categoryOptions(): array
    {
        return [
            ['value' => 'vehicle', 'label' => 'Vehicle'],
            ['value' => 'general', 'label' => 'General'],
        ];
    }

    public static function create(): FormSchema
    {
        return FormSchema::make('New Equipment Type')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->setRules('required|string|max:200')
            )
            ->field(
                SelectInput::make('category')
                    ->setLabel('Category')
                    ->setOptions(self::categoryOptions())
                    ->setRules('nullable|string|in:vehicle,general')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('nullable|string|max:250')
            )
            ->field(
                ToggleInput::make('active')
                    ->setLabel('Active')
                    ->setRules('boolean')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Edit Equipment Type')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:200')
            )
            ->field(
                SelectInput::make('category')
                    ->setLabel('Category')
                    ->setOptions(self::categoryOptions())
                    ->setRules('nullable|string|in:vehicle,general')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('nullable|string|max:250')
            )
            ->field(
                ToggleInput::make('active')
                    ->setLabel('Active')
                    ->setRules('boolean')
            );
    }
}
