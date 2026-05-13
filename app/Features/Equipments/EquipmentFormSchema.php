<?php

namespace App\Features\Equipments;

use App\Core\Enums\EquipmentStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, CheckboxInput, NumberInput, TextAreaInput};

class EquipmentFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.equipments.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.equipments.name'))
                    ->setRequired()
                    ->helperText(__('forms.equipments.name_helper'))
                    ->setRules('required|string|max:200')
            )
            ->field(
                TextInput::make('brand')
                    ->setLabel(__('forms.equipments.brand'))
                    ->helperText(__('forms.equipments.brand_helper'))
                    ->helpExamples(['DeWalt', 'Makita', 'Bosch'])
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('model')
                    ->setLabel(__('forms.equipments.model'))
                    ->helperText(__('forms.equipments.model_helper'))
                    ->helpExamples(['DCD790D2', 'CT440', 'GBH 240'])
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel(__('forms.equipments.serial_number'))
                    ->setRequired()
                    ->helperText(__('forms.equipments.serial_number_helper'))
                    ->setRules('required|string|max:250|unique:equipments,serial_number')
            )
            ->field(
                CheckboxInput::make('is_loanable')
                    ->setLabel(__('forms.equipments.is_loanable'))
                    ->helperText(__('forms.equipments.is_loanable_helper'))
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval_days')
                    ->setLabel(__('forms.equipments.revision_interval'))
                    ->helperText(__('forms.equipments.revision_interval_helper'))
                    ->helpExamples(['30', '90', '365'])
                    ->range(1, null)
                    ->setRules('nullable|integer|min:1')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.equipments.description'))
                    ->helperText(__('forms.equipments.description_helper'))
                    ->setRules('nullable|string|max:250')
            )
            ->field(
                NumberInput::make('cost_per_hour')
                    ->setLabel(__('forms.equipments.cost_per_hour'))
                    ->setRequired()
                    ->setRules('required|numeric|min:0|max:9999.99')
            );
    }

    public static function update(): FormSchema
    {
        $statusOptions = array_map(
            fn(EquipmentStatus $s) => ['value' => $s->value, 'label' => $s->label()],
            EquipmentStatus::cases()
        );
        $statusInRule = implode(',', array_map(fn(EquipmentStatus $s) => $s->value, EquipmentStatus::cases()));

        return FormSchema::make(__('forms.equipments.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.equipments.name'))
                    ->setRules('sometimes|string|max:200')
            )
            ->field(
                TextInput::make('brand')
                    ->setLabel(__('forms.equipments.brand'))
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('model')
                    ->setLabel(__('forms.equipments.model'))
                    ->setRules('nullable|string|max:150')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel(__('forms.equipments.serial_number'))
                    ->setRules('sometimes|string|max:250|unique:equipments,serial_number')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.equipments.status'))
                    ->setOptions($statusOptions)
                    ->setRules("sometimes|in:{$statusInRule}")
            )
            ->field(
                CheckboxInput::make('is_loanable')
                    ->setLabel(__('forms.equipments.is_loanable'))
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval_days')
                    ->setLabel(__('forms.equipments.revision_interval'))
                    ->setRules('nullable|integer|min:1')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.equipments.description'))
                    ->setRules('nullable|string|max:250')
            )
            ->field(
                NumberInput::make('cost_per_hour')
                    ->setLabel(__('forms.equipments.cost_per_hour'))
                    ->setRules('sometimes|numeric|min:0|max:9999.99')
            );
    }
}
