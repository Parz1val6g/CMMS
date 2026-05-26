<?php

namespace App\Features\LoanOrders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\LoanOrderStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput, SectionHeader, MapInput, ToggleInput, RepeaterInput};
use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\Parish;
use App\Shared\Models\User;

class LoanOrderFormSchema
{
    public static function create(): FormSchema
    {
        $equipmentOpts = self::equipmentOptions();

        return FormSchema::make(__('forms.loan_orders.create_title'))
            ->setColumns(2)
            // ── Column 1: Requester ──
            ->field(
                SectionHeader::make('section-requester')
                    ->setLabel(__('forms.loan_orders.section_requester'))
                    ->setColumn(1)
            )
            ->field(
                SelectInput::make('entity_id')
                    ->setLabel(__('forms.loan_orders.entity'))
                    ->helperText(__('forms.loan_orders.entity_helper'))
                    ->setOptions(self::entityOptions())
                    ->setColumn(1)
                    ->setRequired(true)
                    ->setRules('required|uuid|exists:entities,id')
            )
            // ── Column 1: Manager ──
            ->field(
                SectionHeader::make('section-manager')
                    ->setLabel(__('forms.loan_orders.section_manager'))
                    ->setColumn(1)
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.loan_orders.manager'))
                    ->helperText(__('forms.loan_orders.manager_helper'))
                    ->setOptions(self::managerOptions())
                    ->setColumn(1)
                    ->setRequired(true)
                    ->setRules('required|uuid|exists:users,id')
            )
            // ── Column 2: Address ──
            ->field(
                SectionHeader::make('section-address')
                    ->setLabel(__('forms.loan_orders.section_address'))
                    ->setColumn(2)
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.loan_orders.parish'))
                    ->helperText(__('forms.loan_orders.parish_helper'))
                    ->setOptions(self::parishOptions())
                    ->setColumn(2)
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.loan_orders.street'))
                    ->helperText(__('forms.loan_orders.street_helper'))
                    ->setColumn(2)
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.loan_orders.reference_point'))
                    ->helperText(__('forms.loan_orders.reference_point_helper'))
                    ->setColumn(2)
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.loan_orders.postal_code'))
                    ->helperText(__('forms.loan_orders.postal_code_helper'))
                    ->helpExamples(['1000-001', '4000-001'])
                    ->setColumn(2)
                    ->setRules('nullable|string|max:20')
            )
            // ── Full-width: Equipment ──
            ->field(
                SectionHeader::make('section-equipment')
                    ->setLabel(__('forms.loan_orders.section_equipment'))
                    ->setColSpan(2)
            )
            ->field(
                RepeaterInput::make('equipments')
                    ->setColSpan(2)
                    ->setMaxItems(5)
                    ->meta('itemColumns', 2)
                    ->subFields([
                        SelectInput::make('equipment_id')
                            ->setLabel(__('forms.loan_orders.equipment'))
                            ->setOptions($equipmentOpts)
                            ->setRequired(true)
                            ->setRules('required|uuid|exists:equipments,id'),
                        ToggleInput::make('needs_operator')
                            ->setLabel(__('forms.loan_orders.needs_operator'))
                            ->setRules('nullable|boolean'),
                        TextInput::make('start_date')
                            ->setLabel(__('forms.loan_orders.start_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date'),
                        TextInput::make('end_date')
                            ->setLabel(__('forms.loan_orders.end_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date|after_or_equal:start_date'),
                    ])
            )
            // ── Full-width: Map ──
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel(__('forms.loan_orders.section_map'))
                    ->setColSpan(2)
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.loan_orders.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            )
            // ── Full-width: Description ──
            ->field(
                SectionHeader::make('section-description')
                    ->setLabel(__('forms.loan_orders.section_description'))
                    ->setColSpan(2)
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.loan_orders.description'))
                    ->helperText(__('forms.loan_orders.description_helper'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            );
    }

    public static function entityCreate(): FormSchema
    {
        $equipmentOpts = self::equipmentOptions();

        return FormSchema::make(__('forms.loan_orders.create_title'))
            ->setColumns(2)
            ->field(
                SectionHeader::make('section-address')
                    ->setLabel(__('forms.loan_orders.section_address'))
                    ->setColumn(1)
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.loan_orders.parish'))
                    ->helperText(__('forms.loan_orders.parish_helper'))
                    ->setOptions(self::parishOptions())
                    ->setColumn(1)
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.loan_orders.street'))
                    ->setColumn(1)
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.loan_orders.postal_code'))
                    ->setColumn(1)
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SectionHeader::make('section-equipment')
                    ->setLabel(__('forms.loan_orders.section_equipment'))
                    ->setColSpan(2)
            )
            ->field(
                RepeaterInput::make('equipments')
                    ->setColSpan(2)
                    ->setMaxItems(5)
                    ->meta('itemColumns', 2)
                    ->subFields([
                        SelectInput::make('equipment_id')
                            ->setLabel(__('forms.loan_orders.equipment'))
                            ->setOptions($equipmentOpts)
                            ->setRequired(true)
                            ->setRules('required|uuid|exists:equipments,id'),
                        ToggleInput::make('needs_operator')
                            ->setLabel(__('forms.loan_orders.needs_operator'))
                            ->setRules('nullable|boolean'),
                        TextInput::make('start_date')
                            ->setLabel(__('forms.loan_orders.start_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date'),
                        TextInput::make('end_date')
                            ->setLabel(__('forms.loan_orders.end_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date|after_or_equal:start_date'),
                    ])
            )
            ->field(
                SectionHeader::make('section-description')
                    ->setLabel(__('forms.loan_orders.section_description'))
                    ->setColSpan(2)
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.loan_orders.description'))
                    ->helperText(__('forms.loan_orders.description_helper'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.loan_orders.edit_title'))
            ->field(
                SectionHeader::make('section-requester')
                    ->setLabel(__('forms.loan_orders.section_requester'))
            )
            ->field(
                SelectInput::make('entity_id')
                    ->setLabel(__('forms.loan_orders.entity'))
                    ->setOptions(self::entityOptions())
                    ->setRules('sometimes|nullable|uuid|exists:entities,id')
            )
            ->field(
                SectionHeader::make('section-equipment')
                    ->setLabel(__('forms.loan_orders.section_equipment'))
            )
            ->field(
                RepeaterInput::make('equipments')
                    ->setMaxItems(5)
                    ->meta('itemColumns', 2)
                    ->subFields([
                        SelectInput::make('equipment_id')
                            ->setLabel(__('forms.loan_orders.equipment'))
                            ->setOptions(self::editEquipmentOptions())
                            ->setRequired(true)
                            ->setRules('required|uuid|exists:equipments,id'),
                        ToggleInput::make('needs_operator')
                            ->setLabel(__('forms.loan_orders.needs_operator'))
                            ->setRules('nullable|boolean'),
                        TextInput::make('start_date')
                            ->setLabel(__('forms.loan_orders.start_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date'),
                        TextInput::make('end_date')
                            ->setLabel(__('forms.loan_orders.end_date'))
                            ->setType('date-picker')
                            ->setRules('nullable|date|after_or_equal:start_date'),
                    ])
            )
            ->field(
                SectionHeader::make('section-manager')
                    ->setLabel(__('forms.loan_orders.section_manager'))
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.loan_orders.manager'))
                    ->setOptions(self::managerOptions())
                    ->setRules('sometimes|uuid|exists:users,id')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.loan_orders.status'))
                    ->setOptions(LoanOrderStatus::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.loan_orders.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.loan_orders.parish'))
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.loan_orders.street'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.loan_orders.reference_point'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.loan_orders.postal_code'))
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel(__('forms.loan_orders.section_map'))
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.loan_orders.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            )
            ->field(
                SectionHeader::make('section-description')
                    ->setLabel(__('forms.loan_orders.section_description'))
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.loan_orders.description'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            );
    }

    private static function equipmentOptions(): array
    {
        return Equipment::loanable()
            ->where('status', EquipmentStatus::ACTIVE->value)
            ->where(function ($q) {
                $q->whereNull('next_revision_date')
                  ->orWhere('next_revision_date', '>=', now());
            })
            ->get(['id', 'name', 'brand', 'model'])
            ->map(fn($e) => [
                'value' => $e->id,
                'label' => "{$e->name} ({$e->brand} {$e->model})",
            ])
            ->toArray();
    }

    private static function editEquipmentOptions(): array
    {
        return Equipment::loanable()
            ->whereIn('status', [EquipmentStatus::ACTIVE->value, EquipmentStatus::IN_USE->value])
            ->get(['id', 'name', 'brand', 'model'])
            ->map(fn($e) => [
                'value' => $e->id,
                'label' => "{$e->name} ({$e->brand} {$e->model})",
            ])
            ->toArray();
    }

    private static function entityOptions(): array
    {
        return Entity::orderBy('name')->get(['id', 'name'])
            ->map(fn($e) => ['value' => $e->id, 'label' => $e->name])
            ->toArray();
    }

    private static function parishOptions(): array
    {
        return Parish::orderBy('name')->get(['id', 'name'])
            ->map(fn($p) => ['value' => $p->id, 'label' => $p->name])
            ->toArray();
    }

    private static function managerOptions(): array
    {
        return User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager', 'task_manager']))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($u) => [
                'value' => $u->id,
                'label' => $u->first_name . ' ' . $u->last_name,
            ])
            ->toArray();
    }
}
