<?php

namespace App\Features\ServiceOrders\Schemas;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput, FileInput, SectionHeader, MapInput};
use App\Features\Clients\Models\Client;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class ServiceOrderFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Nova Ordem de Serviço')
            // ── Workflow Type (MUST be first — drives conditional UI) ──
            ->field(
                SectionHeader::make('section-core')
                    ->setLabel('Core Details')
            )
            ->field(
                SelectInput::make('workflow_type')
                    ->setLabel('Workflow Type')
                    ->helperText('Choose between Regular service or Equipment Loan')
                    ->setOptions(WorkflowType::options())
                    ->setRequired()
                    ->setRules('required|string')
            )
            ->field(
                TextInput::make('process')
                    ->setLabel('Process')
                    ->setRequired()
                    ->helperText('Brief title or identifier for this service order')
                    ->setRules('required|string|max:250')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->helperText('Detailed description of the work to be performed')
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel('Client')
                    ->helperText('Select the client requesting this service (optional)')
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('equipment_id')
                    ->setLabel('Equipment')
                    ->helperText('Equipment involved in this service (optional)')
                    ->setOptions(self::equipmentOptions())
                    ->setRules('nullable|exists:equipments,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel('Service Type')
                    ->helperText('Type of service being provided')
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel('Priority')
                    ->helperText('Set the urgency level for this service order')
                    ->setOptions(Priority::options())
                    ->setRules('nullable|string')
            )
            // ── Photo ──
            ->field(
                SectionHeader::make('section-photo')
                    ->setLabel('Photo')
            )
            ->field(
                FileInput::make('photo')
                    ->setLabel('Upload Photo')
                    ->helperText('Upload a photo of the service location (max 5MB)')
                    ->setRules('nullable|image|mimes:jpeg,png,jpg|max:5120')
                    ->meta('accept', 'image/jpeg,image/png')
            )
            // ── Smart Location Group ──
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel('Location')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel('Parish / Freguesia')
                    ->helperText('Select the parish/region where the service will occur')
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel('Street')
                    ->helperText('Street address (optional)')
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel('Reference Point')
                    ->helperText('Landmark or additional location details (optional)')
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel('Postal Code')
                    ->helperText('Format: XXXX-XXX')
                    ->helpExamples(['1000-001', '4000-001'])
                    ->setRules('nullable|string|max:20')
            )
            // ── Map Picker ──
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel('Map Coordinates')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel('Coordinates')
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Ordem de Serviço')
            // ── Workflow Type (MUST be first — drives conditional UI) ──
            ->field(
                SectionHeader::make('section-core')
                    ->setLabel('Core Details')
            )
            ->field(
                SelectInput::make('workflow_type')
                    ->setLabel('Workflow Type')
                    ->setOptions(WorkflowType::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                TextInput::make('process')
                    ->setLabel('Process')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('nullable|string|max:2000')
            )
            ->field(
                SelectInput::make('client_id')
                    ->setLabel('Client')
                    ->setOptions(self::clientOptions())
                    ->setRules('nullable|exists:clients,id')
            )
            ->field(
                SelectInput::make('equipment_id')
                    ->setLabel('Equipment')
                    ->setOptions(self::equipmentOptions())
                    ->setRules('nullable|exists:equipments,id')
            )
            ->field(
                SelectInput::make('service_type_id')
                    ->setLabel('Service Type')
                    ->setOptions(self::serviceTypeOptions())
                    ->setRules('nullable|exists:service_types,id')
            )
            ->field(
                SelectInput::make('priority')
                    ->setLabel('Priority')
                    ->setOptions(Priority::options())
                    ->setRules('nullable|string')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel('Status')
                    ->setOptions(ServiceOrderStatus::options())
                    ->setRules('sometimes|string')
            )
            ->field(
                TextInput::make('execution_date')
                    ->setLabel('Execution Date')
                    ->setType('date')
                    ->setRules('nullable|date')
            )
            // ── Photo ──
            ->field(
                SectionHeader::make('section-photo')
                    ->setLabel('Photo')
            )
            ->field(
                FileInput::make('photo')
                    ->setLabel('Upload Photo')
                    ->setRules('nullable|image|mimes:jpeg,png,jpg|max:5120')
                    ->meta('accept', 'image/jpeg,image/png')
            )
            // ── Smart Location Group ──
            ->field(
                SectionHeader::make('section-location')
                    ->setLabel('Location')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel('Parish / Freguesia')
                    ->setOptions(self::parishOptions())
                    ->setRules('nullable|exists:parishes,id')
            )
            ->field(
                TextInput::make('street')
                    ->setLabel('Street')
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('reference_point')
                    ->setLabel('Reference Point')
                    ->setRules('nullable|string|max:255')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel('Postal Code')
                    ->setRules('nullable|string|max:20')
            )
            // ── Map Coordinates ──
            ->field(
                SectionHeader::make('section-map')
                    ->setLabel('Map Coordinates')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel('Coordinates')
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    private static function equipmentOptions(): array
    {
        return \App\Features\Equipments\Models\Equipment::loanable()
            ->get(['id', 'name', 'brand', 'model'])
            ->map(fn($e) => [
                'value' => $e->id,
                'label' => "{$e->name} ({$e->brand} {$e->model})",
            ])
            ->toArray();
    }

    private static function clientOptions(): array
    {
        return Client::with('user')->get()
            ->map(fn($c) => [
                'value' => $c->id,
                'label' => ($c->user?->first_name ?? '') . ' ' . ($c->user?->last_name ?? ''),
            ])
            ->toArray();
    }

    private static function serviceTypeOptions(): array
    {
        return ServiceType::all()
            ->map(fn($st) => ['value' => $st->id, 'label' => $st->name])
            ->toArray();
    }

    private static function parishOptions(): array
    {
        return Parish::orderBy('name')->get()
            ->map(fn($p) => ['value' => $p->id, 'label' => $p->name])
            ->toArray();
    }
}
