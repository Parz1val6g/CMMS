<?php

namespace App\Features\Equipments;

use App\Core\Enums\EquipmentStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, ToggleInput, NumberInput, TextAreaInput};
use App\Features\Equipments\Models\CountingType;
use App\Features\Equipments\Models\EquipmentType;

class EquipmentFormSchema
{
    private static function equipmentTypeOptions(): array
    {
        return EquipmentType::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'category'])
            ->map(fn ($et) => ['value' => $et->id, 'label' => $et->name . ($et->category ? " ({$et->category})" : '')])
            ->toArray();
    }

    private static function countingTypeOptions(): array
    {
        return CountingType::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($ct) => ['value' => $ct->id, 'label' => $ct->name])
            ->toArray();
    }

    private static function vehicleTypeIds(): array
    {
        return EquipmentType::where('active', true)
            ->where('category', 'vehicle')
            ->pluck('id')
            ->toArray();
    }

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
                SelectInput::make('equipment_type_id')
                    ->setLabel(__('forms.equipments.equipment_type'))
                    ->setOptions(self::equipmentTypeOptions())
                    ->setRequired()
                    ->setRules('required|string|max:36')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel(__('forms.equipments.serial_number'))
                    ->helperText(__('forms.equipments.serial_number_helper'))
                    ->setRules('nullable|string|max:250|unique:equipments,serial_number')
                    ->when('equipment_type_id', 'not_in', self::vehicleTypeIds())
            )
            ->field(
                TextInput::make('license_plate')
                    ->setLabel(__('forms.equipments.license_plate'))
                    ->helperText(__('forms.equipments.license_plate_helper'))
                    ->setRules('nullable|string|max:20')
                    ->when('equipment_type_id', 'in', self::vehicleTypeIds())
            )
            ->field(
                TextInput::make('internal_reference')
                    ->setLabel(__('forms.equipments.internal_reference'))
                    ->helperText(__('forms.equipments.internal_reference_helper'))
                    ->setRules('nullable|string|max:250|unique:equipments,internal_reference')
            )
            ->field(
                NumberInput::make('manufacturing_year')
                    ->setLabel(__('forms.equipments.manufacturing_year'))
                    ->helperText(__('forms.equipments.manufacturing_year_helper'))
                    ->range(1900, 2099)
                    ->setRules('nullable|integer|min:1900|max:2099')
            )
            ->field(
                TextInput::make('inspection_date')
                    ->setLabel(__('forms.equipments.inspection_date'))
                    ->helperText(__('forms.equipments.inspection_date_helper'))
                    ->setRules('nullable|date')
            )
            ->field(
                SelectInput::make('counting_type_id')
                    ->setLabel(__('forms.equipments.counting_type'))
                    ->setOptions(self::countingTypeOptions())
                    ->helperText(__('forms.equipments.counting_type_helper'))
                    ->setRules('nullable|string|max:36')
            )
            ->field(
                ToggleInput::make('is_loanable')
                    ->setLabel(__('forms.equipments.is_loanable'))
                    ->helperText(__('forms.equipments.is_loanable_helper'))
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval')
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
                    ->step(0.01)
                    ->min(0)
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
                SelectInput::make('equipment_type_id')
                    ->setLabel(__('forms.equipments.equipment_type'))
                    ->setOptions(self::equipmentTypeOptions())
                    ->setRules('sometimes|string|max:36')
            )
            ->field(
                TextInput::make('serial_number')
                    ->setLabel(__('forms.equipments.serial_number'))
                    ->helperText(__('forms.equipments.serial_number_helper'))
                    ->setRules('nullable|string|max:250|unique:equipments,serial_number')
                    ->when('equipment_type_id', 'not_in', self::vehicleTypeIds())
            )
            ->field(
                TextInput::make('license_plate')
                    ->setLabel(__('forms.equipments.license_plate'))
                    ->helperText(__('forms.equipments.license_plate_helper'))
                    ->setRules('nullable|string|max:20')
                    ->when('equipment_type_id', 'in', self::vehicleTypeIds())
            )
            ->field(
                TextInput::make('internal_reference')
                    ->setLabel(__('forms.equipments.internal_reference'))
                    ->setRules('nullable|string|max:250|unique:equipments,internal_reference')
            )
            ->field(
                NumberInput::make('manufacturing_year')
                    ->setLabel(__('forms.equipments.manufacturing_year'))
                    ->range(1900, 2099)
                    ->setRules('nullable|integer|min:1900|max:2099')
            )
            ->field(
                TextInput::make('inspection_date')
                    ->setLabel(__('forms.equipments.inspection_date'))
                    ->setRules('nullable|date')
            )
            ->field(
                SelectInput::make('counting_type_id')
                    ->setLabel(__('forms.equipments.counting_type'))
                    ->setOptions(self::countingTypeOptions())
                    ->setRules('nullable|string|max:36')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.equipments.status'))
                    ->setOptions($statusOptions)
                    ->setRules("sometimes|in:{$statusInRule}")
            )
            ->field(
                ToggleInput::make('is_loanable')
                    ->setLabel(__('forms.equipments.is_loanable'))
                    ->setRules('boolean')
            )
            ->field(
                NumberInput::make('revision_interval')
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
                    ->step(0.01)
                    ->min(0)
                    ->setRules('sometimes|numeric|min:0|max:9999.99')
            );
    }
}
