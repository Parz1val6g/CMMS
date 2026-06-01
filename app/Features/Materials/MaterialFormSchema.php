<?php

namespace App\Features\Materials;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, NumberInput};
use App\Core\Cache\RefCache;

class MaterialFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.materials.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.materials.name'))
                    ->setRequired()
                    ->helperText(__('forms.materials.name_helper'))
                    ->helpExamples(['Concrete Blocks', 'Copper Wire', 'Paint (Acrylic)'])
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('unit_id')
                    ->setLabel(__('forms.materials.unit'))
                    ->helperText(__('forms.materials.unit_helper'))
                    ->setOptions(self::unitOptions())
                    ->setRules('required|exists:units,id')
            )
            ->field(
                NumberInput::make('stock_quantity')
                    ->setLabel(__('forms.materials.stock_quantity'))
                    ->setRequired()
                    ->helperText(__('forms.materials.stock_quantity_helper'))
                    ->range(0, null)
                    ->setRules('required|numeric|min:0')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.materials.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.materials.name'))
                    ->helperText(__('forms.materials.name_helper'))
                    ->helpExamples(['Concrete Blocks', 'Copper Wire', 'Paint (Acrylic)'])
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('unit_id')
                    ->setLabel(__('forms.materials.unit'))
                    ->helperText(__('forms.materials.unit_helper'))
                    ->setOptions(self::unitOptions())
                    ->setRules('sometimes|exists:units,id')
            )
            ->field(
                NumberInput::make('stock_quantity')
                    ->setLabel(__('forms.materials.stock_quantity'))
                    ->helperText(__('forms.materials.stock_quantity_helper'))
                    ->range(0, null)
                    ->setRules('sometimes|numeric|min:0')
            );
    }

    private static function unitOptions(): array
    {
        return RefCache::units();
    }
}
