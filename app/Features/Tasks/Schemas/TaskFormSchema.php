<?php

namespace App\Features\Tasks\Schemas;

use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput};
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Sectors\Models\Sector;

class TaskFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Nova Tarefa')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->helperText('Brief name or title for this task')
                    ->setRules('required|string|max:150')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->helperText('Detailed description of what needs to be done')
                    ->setRows(3)
                    ->setRules('nullable|string|max:1000')
            )
            ->field(
                SelectInput::make('service_order_id')
                    ->setLabel('Service Order')
                    ->helperText('Select the service order this task belongs to')
                    ->setOptions(self::serviceOrderOptions())
                    ->setRules('required|exists:service_orders,id')
            )
            ->field(
                SelectInput::make('sector_ids')
                    ->setLabel('Sectors')
                    ->helperText('Assign this task to one or more sectors')
                    ->setOptions(self::sectorOptions())
                    ->meta('multiple', true)
                    ->setRules('required|array|min:1')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Tarefa')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->helperText('Brief name or title for this task')
                    ->setRules('sometimes|string|max:150')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->helperText('Detailed description of what needs to be done')
                    ->setRows(3)
                    ->setRules('nullable|string|max:1000')
            )
            ->field(
                SelectInput::make('service_order_id')
                    ->setLabel('Service Order')
                    ->helperText('Select the service order this task belongs to')
                    ->setOptions(self::serviceOrderOptions())
                    ->setRules('sometimes|exists:service_orders,id')
            )
            ->field(
                SelectInput::make('sector_ids')
                    ->setLabel('Sectors')
                    ->helperText('Assign this task to one or more sectors')
                    ->setOptions(self::sectorOptions())
                    ->meta('multiple', true)
                    ->setRules('sometimes|array|min:1')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel('Status')
                    ->helperText('Current status of the task')
                    ->setOptions(TaskStatus::options())
                    ->setRules('sometimes|string')
            );
    }

    private static function serviceOrderOptions(): array
    {
        return ServiceOrder::orderBy('process')
            ->get()
            ->map(fn($so) => ['value' => $so->id, 'label' => $so->process])
            ->toArray();
    }

    private static function sectorOptions(): array
    {
        return Sector::orderBy('name')
            ->get()
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();
    }
}
