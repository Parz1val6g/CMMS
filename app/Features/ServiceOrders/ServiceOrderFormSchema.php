<?php

namespace App\Features\ServiceOrders;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput, FileInput, SectionHeader, MapInput};
use App\Features\Clients\Models\Client;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;
use App\Shared\Models\User;

class ServiceOrderFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.service_orders.create_title'))
            // ── Core fields ──
            ->field(
                SectionHeader::make('section-core')
                    ->setLabel(__('forms.service_orders.section_core'))
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_orders.description'))
                    ->helperText(__('forms.service_orders.description_helper'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            )
            ->field(
                TextInput::make('execution_date')
                    ->setLabel(__('forms.service_orders.execution_date'))
                    ->setType('date-picker')
                    ->setRequired()
                    ->setRules('required|date')
            )
            ->field(
                SelectInput::make('sector_ids')
                    ->setLabel(__('forms.service_orders.sectors'))
                    ->setRequired()
                    ->helperText(__('forms.service_orders.sectors_helper'))
                    ->setOptions(self::sectorOptions())
                    ->multiple()
                    ->setRules('required|array|min:1')
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.service_orders.manager'))
                    ->helperText(__('forms.service_orders.manager_helper'))
                    ->setOptions(self::managerOptions())
                    ->setRules('required|uuid|exists:users,id')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.service_orders.client'))
                    ->helperText(__('forms.service_orders.client_helper'))
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel(__('forms.service_orders.service_type'))
                    ->helperText(__('forms.service_orders.service_type_helper'))
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel(__('forms.service_orders.priority'))
                    ->helperText(__('forms.service_orders.priority_helper'))
                    ->setOptions(Priority::options())
                    ->setRules('nullable|string')
            )
            // ── Photo ──
            ->field(
                SectionHeader::make('section-photo')
                    ->setLabel(__('forms.service_orders.section_photo'))
            )
            ->field(
                FileInput::make('photo')
                    ->setLabel(__('forms.service_orders.upload_photo'))
                    ->helperText(__('forms.service_orders.upload_photo_helper'))
                    ->setRules('nullable|image|mimes:jpeg,png,jpg|max:5120')
                    ->meta('accept', 'image/jpeg,image/png')
            )
            // ── Smart Location Group ──
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.service_orders.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.service_orders.parish'))
                    ->helperText(__('forms.service_orders.parish_helper'))
                    ->setOptions(self::parishOptions())
                    ->setRequired()
                    ->setRules('required_without:client_location_id|uuid|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.service_orders.street'))
                    ->helperText(__('forms.service_orders.street_helper'))
                    ->setRequired()
                    ->setRules('required_without:client_location_id|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.service_orders.reference_point'))
                    ->helperText(__('forms.service_orders.reference_point_helper'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.service_orders.postal_code'))
                    ->helperText(__('forms.service_orders.postal_code_helper'))
                    ->helpExamples(['1000-001', '4000-001'])
                    ->setRules('nullable|string|max:20')
            )
            // ── Map Picker ──
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel(__('forms.service_orders.section_map'))
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.service_orders.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.service_orders.edit_title'))
            // ── Core fields ──
            ->field(
                SectionHeader::make('section-core')
                    ->setLabel(__('forms.service_orders.section_core'))
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_orders.description'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            )
            ->field(
                SelectInput::make('sector_ids')
                    ->setLabel(__('forms.service_orders.sectors'))
                    ->helperText(__('forms.service_orders.sectors_helper'))
                    ->setOptions(self::sectorOptions())
                    ->multiple()
                    ->setRules('sometimes|array|min:1')
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.service_orders.manager'))
                    ->setOptions(self::managerOptions())
                    ->setRules('sometimes|uuid|exists:users,id')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.service_orders.client'))
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel(__('forms.service_orders.service_type'))
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel(__('forms.service_orders.priority'))
                    ->setOptions(Priority::options())
                    ->setRules('nullable|string')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.service_orders.status'))
                    ->setOptions(ServiceOrderStatus::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                TextInput::make('execution_date')
                    ->setLabel(__('forms.service_orders.execution_date'))
                    ->setType('date-picker')
                    ->setRules('nullable|date')
            )
            // ── Photo ──
            ->field(
                SectionHeader::make('section-photo')
                    ->setLabel(__('forms.service_orders.section_photo'))
            )
            ->field(
                FileInput::make('photo')
                    ->setLabel(__('forms.service_orders.upload_photo'))
                    ->setRules('nullable|image|mimes:jpeg,png,jpg|max:5120')
                    ->meta('accept', 'image/jpeg,image/png')
            )
            // ── Smart Location Group ──
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.service_orders.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.service_orders.parish'))
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.service_orders.street'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.service_orders.reference_point'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.service_orders.postal_code'))
                    ->setRules('nullable|string|max:20')
            )
            // ── Map Coordinates ──
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel(__('forms.service_orders.section_map'))
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.service_orders.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    private static function sectorOptions(): array
    {
        return Sector::orderBy('name')->get(['id', 'name'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
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

    private static function serviceTypeOptions(): array
    {
        return ServiceType::orderBy('name')->get(['id', 'name'])
            ->map(fn($st) => ['value' => $st->id, 'label' => $st->name])
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
