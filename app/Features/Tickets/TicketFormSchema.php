<?php

namespace App\Features\Tickets;

use App\Core\Enums\TicketPriority;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextAreaInput, SelectInput, TextInput, SectionHeader, MapInput};
use App\Features\Clients\Models\Client;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class TicketFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.tickets.create_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tickets.description'))
                    ->helperText(__('forms.tickets.description_helper'))
                    ->setRows(4)
                    ->setRequired()
                    ->setRules('required|string|max:5000')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.tickets.client'))
                    ->helperText(__('forms.tickets.client_helper'))
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel(__('forms.tickets.service_type'))
                    ->helperText(__('forms.tickets.service_type_helper'))
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel(__('forms.tickets.priority'))
                    ->helperText(__('forms.tickets.priority_helper'))
                    ->setOptions(TicketPriority::options())
                    ->setRequired()
                    ->setRules('required|string')
            )
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.tickets.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.tickets.parish'))
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.tickets.street'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.tickets.reference_point'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.tickets.postal_code'))
                    ->setRules('nullable|string|max:20')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.tickets.edit_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tickets.description'))
                    ->setRows(4)
                    ->setRules('sometimes|string|max:5000')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel(__('forms.tickets.client'))
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel(__('forms.tickets.service_type'))
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel(__('forms.tickets.priority'))
                    ->setOptions(TicketPriority::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel(__('forms.tickets.section_location'))
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.tickets.parish'))
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel(__('forms.tickets.street'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel(__('forms.tickets.reference_point'))
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.tickets.postal_code'))
                    ->setRules('nullable|string|max:20')
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
}
