<?php

namespace App\Features\Materials\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, NumberInput};
use App\Shared\Models\Unit;

class MaterialFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Material')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->helperText('Name of the material or supply item')
                    ->helpExamples(['Concrete Blocks', 'Copper Wire', 'Paint (Acrylic)'])
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('unit_id')
                    ->setLabel('Unit')
                    ->helperText('Unit of measurement for this material')
                    ->setOptions(self::unitOptions())
                    ->setRules('required|exists:units,id')
            )
            ->field(
                NumberInput::make('stock_quantity')
                    ->setLabel('Stock Quantity')
                    ->setRequired()
                    ->helperText('Current stock quantity')
                    ->range(0, null)
                    ->setRules('required|numeric|min:0')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Material')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->helperText('Name of the material or supply item')
                    ->helpExamples(['Concrete Blocks', 'Copper Wire', 'Paint (Acrylic)'])
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('unit_id')
                    ->setLabel('Unit')
                    ->helperText('Unit of measurement for this material')
                    ->setOptions(self::unitOptions())
                    ->setRules('sometimes|exists:units,id')
            )
            ->field(
                NumberInput::make('stock_quantity')
                    ->setLabel('Stock Quantity')
                    ->helperText('Current stock quantity')
                    ->range(0, null)
                    ->setRules('sometimes|numeric|min:0')
            );
    }

    private static function unitOptions(): array
    {
        return Unit::all()
            ->map(fn($u) => ['value' => $u->id, 'label' => $u->name . ' (' . $u->abbreviation . ')'])
            ->toArray();
    }
}
