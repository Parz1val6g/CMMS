<?php

namespace App\Features\Equipments\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, CheckboxInput, NumberInput, TextAreaInput};

class EquipmentFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Equipamento')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->helperText('Enter the equipment name (e.g., "Industrial Drill", "Compressor")')
                    ->setRules('required|string|max:200')
            )
            ->field(
                TextInput::make('brand')
                    ->setLabel('Brand')
                    ->helperText('Equipment brand/manufacturer (optional)')
                    ->helpExamples(['DeWalt', 'Makita', 'Bosch'])
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('model')
                    ->setLabel('Model')
                    ->helperText('Equipment model number (optional)')
                    ->helpExamples(['DCD790D2', 'CT440', 'GBH 240'])
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel('Serial Number')
                    ->setRequired()
                    ->helperText('Unique serial number/identifier for this equipment')
                    ->setRules('required|string|max:250|unique:equipments,serial_number')
            )
            ->field(
                CheckboxInput::make('is_loanable')
                    ->setLabel('Available for Loan')
                    ->helperText('Mark if this equipment can be loaned out')
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval_days')
                    ->setLabel('Revision Interval (days)')
                    ->helperText('How often the equipment needs maintenance review')
                    ->helpExamples(['30', '90', '365'])
                    ->range(1, null)
                    ->setRules('nullable|integer|min:1')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->helperText('Additional details about the equipment (optional)')
                    ->setRules('nullable|string|max:250')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Equipamento')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:200')
            )
            ->field(
                TextInput::make('brand')
                    ->setLabel('Brand')
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('model')
                    ->setLabel('Model')
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel('Serial Number')
                    ->setRules('sometimes|string|max:250|unique:equipments,serial_number')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel('Status')
                    ->setOptions([
                        ['value' => 'active', 'label' => 'Active'],
                        ['value' => 'in_use', 'label' => 'In Use'],
                        ['value' => 'maintenance', 'label' => 'Maintenance'],
                        ['value' => 'retired', 'label' => 'Retired'],
                    ])
                    ->setRules('sometimes|in:active,in_use,maintenance,retired')
            )
            ->field(
                CheckboxInput::make('is_loanable')
                    ->setLabel('Available for Loan')
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval_days')
                    ->setLabel('Revision Interval (days)')
                    ->setRules('nullable|integer|min:1')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRules('nullable|string|max:250')
            );
    }
}
