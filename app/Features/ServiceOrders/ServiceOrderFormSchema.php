<?php

namespace App\Features\ServiceOrders;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput, FileInput, SectionHeader, MapInput, DateRangeInput};
use App\Core\Cache\RefCache;
use App\Core\LocationCascadeOptions;
use App\Features\Clients\Models\Client;
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
                TextInput::make('title')
                    ->setLabel(__('forms.service_orders.title'))
                    ->helperText(__('forms.service_orders.title_helper'))
                    ->setRules('string|max:255')
                    ->setRequired()
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_orders.description'))
                    ->helperText(__('forms.service_orders.description_helper'))
                    ->setRows(3)
                    ->setRules('string|max:2000')
                    ->setRequired()
            )
            ->field(
                DateRangeInput::make('date_range')
                    ->setLabel(__('forms.service_orders.start_date'))
                    ->setRequired()
                    ->setStartName('start_date')
                    ->setEndName('end_date')
                    ->setRules('date')
                    ->setRequired()
            )
            ->field(
                SelectInput::make('manager_id')
                    ->setLabel(__('forms.service_orders.manager'))
                    ->helperText(__('forms.service_orders.manager_helper'))
                    ->setOptions(self::managerOptions())
                    ->setRules('uuid|exists:users,id')
                    ->setRequired()
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.service_orders.client'))
                    ->helperText(__('forms.service_orders.client_helper'))
                    ->setOptions(self::clientOptions())
                    ->setRules('exists:clients,id')
            )
            ->field(
                SelectInput::make('category_id')
                    ->setLabel(__('forms.service_orders.category'))
                    ->helperText(__('forms.service_orders.category_helper'))
                    ->setOptions(RefCache::serviceOrderCategories())
                    ->setRules('uuid|exists:service_order_categories,id')
                    ->setRequired()
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
                    ->setRules('image|mimes:jpeg,png,jpg|max:5120')
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
                    ->setRules('required_without:client_location_id|uuid|exists:parishes,id')
                    ->meta('useCascade', true)
                    ->meta('districts', LocationCascadeOptions::all()['districts'])
                    ->meta('municipalities', LocationCascadeOptions::all()['municipalities'])
                    ->meta('parishes', LocationCascadeOptions::all()['parishes'])
                    ->setRequired()
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.service_orders.street'))
                    ->helperText(__('forms.service_orders.street_helper'))
                    ->setRules('required_without:client_location_id|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.service_orders.reference_point'))
                    ->helperText(__('forms.service_orders.reference_point_helper'))
                    ->setRules('string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.service_orders.postal_code'))
                    ->helperText(__('forms.service_orders.postal_code_helper'))
                    ->helpExamples(['1000-001', '4000-001'])
                    ->setRules('string|max:20')
                    ->setRequired()
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
                    ->setRules('numeric|between:-90,90')
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
                TextInput::make('title')
                    ->setLabel(__('forms.service_orders.title'))
                    ->setRules('string|max:255')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.service_orders.description'))
                    ->setRows(3)
                    ->setRules('string|max:2000')
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
                    ->setRules('exists:clients,id')
            )
            ->field(
                SelectInput::make('category_id')
                    ->setLabel(__('forms.service_orders.category'))
                    ->setOptions(RefCache::serviceOrderCategories())
                    ->setRules('uuid')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.service_orders.status'))
                    ->setOptions(ServiceOrderStatus::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                DateRangeInput::make('date_range')
                    ->setLabel(__('forms.service_orders.start_date'))
                    ->setStartName('start_date')
                    ->setEndName('end_date')
                    ->setRules('date')
            )
            // ── Photo ──
            ->field(
                SectionHeader::make('section-photo')
                    ->setLabel(__('forms.service_orders.section_photo'))
            )
            ->field(
                FileInput::make('photo')
                    ->setLabel(__('forms.service_orders.upload_photo'))
                    ->setRules('image|mimes:jpeg,png,jpg|max:5120')
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
                    ->setRules('exists:parishes,id')
                    ->meta('useCascade', true)
                    ->meta('districts', LocationCascadeOptions::all()['districts'])
                    ->meta('municipalities', LocationCascadeOptions::all()['municipalities'])
                    ->meta('parishes', LocationCascadeOptions::all()['parishes'])
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.service_orders.street'))
                    ->setRules('string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.service_orders.reference_point'))
                    ->setRules('string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.service_orders.postal_code'))
                    ->setRules('string|max:20')
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
                    ->setRules('numeric|between:-90,90')
            );
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

    private static function parishOptions(): array
    {
        return RefCache::parishes();
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
