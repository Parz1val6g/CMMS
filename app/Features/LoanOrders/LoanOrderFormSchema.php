<?php

namespace App\Features\LoanOrders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\LoanOrderStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput, SectionHeader, MapInput, ToggleInput};
use App\Features\Clients\Models\Client;
use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\Parish;
use App\Shared\Models\User;

class LoanOrderFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.loan_orders.create_title'))
            // ── Requester Section ──
            ->field(
                SectionHeader::make('section-requester')
                    ->setLabel(__('forms.loan_orders.section_requester'))
            )
            ->field(
                SelectInput::make('entity_id')
                    ->setLabel(__('forms.loan_orders.entity'))
                    ->helperText(__('forms.loan_orders.entity_helper'))
                    ->setOptions(self::entityOptions())
                    ->setRules('required_without:client_id|nullable|uuid|exists:entities,id')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.loan_orders.client'))
                    ->helperText(__('forms.loan_orders.client_helper'))
                    ->setOptions(self::clientOptions())
                    ->setRules('required_without:entity_id|nullable|uuid|exists:clients,id')
            )
            // ── Equipment Section ──
            ->field(
                SectionHeader::make('section-equipment')
                    ->setLabel(__('forms.loan_orders.section_equipment'))
            )
            ->field(
                SelectInput::make('equipment_ids')
                    ->setLabel(__('forms.loan_orders.equipment'))
                    ->helperText(__('forms.loan_orders.equipment_helper'))
                    ->setOptions(self::equipmentOptions())
                    ->multiple()
                    ->setRules('required|array|min:1')
            )
            ->field(
                ToggleInput::make('needs_operator')
                    ->setLabel(__('forms.loan_orders.needs_operator'))
                    ->helperText(__('forms.loan_orders.needs_operator_helper'))
                    ->setRules('nullable|boolean')
            )
            // ── Schedule Section ──
            ->field(
                SectionHeader::make('section-schedule')
                    ->setLabel(__('forms.loan_orders.section_schedule'))
            )
            ->field(
                TextInput::make('start_date')
                    ->setLabel(__('forms.loan_orders.start_date'))
                    ->helperText(__('forms.loan_orders.start_date_helper'))
                    ->setType('date')
                    ->setRules('required|date')
            )
            ->field(
                TextInput::make('end_date')
                    ->setLabel(__('forms.loan_orders.end_date'))
                    ->helperText(__('forms.loan_orders.end_date_helper'))
                    ->setType('date')
                    ->setRules('required|date|after_or_equal:start_date')
            )
            // ── Manager Section ──
            ->field(
                SectionHeader::make('section-manager')
                    ->setLabel(__('forms.loan_orders.section_manager'))
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.loan_orders.manager'))
                    ->helperText(__('forms.loan_orders.manager_helper'))
                    ->setOptions(self::managerOptions())
                    ->setRules('required|uuid|exists:users,id')
            )
            // ── Location Section ──
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.loan_orders.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.loan_orders.parish'))
                    ->helperText(__('forms.loan_orders.parish_helper'))
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.loan_orders.street'))
                    ->helperText(__('forms.loan_orders.street_helper'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.loan_orders.reference_point'))
                    ->helperText(__('forms.loan_orders.reference_point_helper'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.loan_orders.postal_code'))
                    ->helperText(__('forms.loan_orders.postal_code_helper'))
                    ->helpExamples(['1000-001', '4000-001'])
                    ->setRules('nullable|string|max:20')
            )
            // ── Map Picker ──
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
            // ── Description Section ──
            ->field(
                SectionHeader::make('section-description')
                    ->setLabel(__('forms.loan_orders.section_description'))
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
                SelectInput::make('client_id')
                    ->setLabel(__('forms.loan_orders.client'))
                    ->setOptions(self::clientOptions())
                    ->setRules('sometimes|nullable|uuid|exists:clients,id')
            )
            ->field(
                SectionHeader::make('section-equipment')
                    ->setLabel(__('forms.loan_orders.section_equipment'))
            )
            ->field(
                SelectInput::make('equipment_ids')
                    ->setLabel(__('forms.loan_orders.equipment'))
                    ->setOptions(self::editEquipmentOptions())
                    ->multiple()
                    ->setRules('sometimes|array|min:1')
            )
            ->field(
                ToggleInput::make('needs_operator')
                    ->setLabel(__('forms.loan_orders.needs_operator'))
                    ->setRules('nullable|boolean')
            )
            ->field(
                SectionHeader::make('section-schedule')
                    ->setLabel(__('forms.loan_orders.section_schedule'))
            )
            ->field(
                TextInput::make('start_date')
                    ->setLabel(__('forms.loan_orders.start_date'))
                    ->setType('date')
                    ->setRules('sometimes|date')
            )
            ->field(
                TextInput::make('end_date')
                    ->setLabel(__('forms.loan_orders.end_date'))
                    ->setType('date')
                    ->setRules('sometimes|date|after_or_equal:start_date')
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

    private static function clientOptions(): array
    {
        return Client::join('users', 'users.id', '=', 'clients.user_id')
            ->orderBy('users.first_name')
            ->get(['clients.id', 'users.first_name', 'users.last_name'])
            ->map(fn($c) => [
                'value' => $c->id,
                'label' => trim("{$c->first_name} {$c->last_name}"),
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
