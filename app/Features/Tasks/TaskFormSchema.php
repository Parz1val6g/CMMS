<?php

namespace App\Features\Tasks;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput};
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Sectors\Models\Sector;

class TaskFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.tasks.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.tasks.name'))
                    ->setRequired()
                    ->helperText(__('forms.tasks.name_helper'))
                    ->setRules('required|string|max:150')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tasks.description'))
                    ->helperText(__('forms.tasks.description_helper'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:1000')
            )
            ->field(
                SelectInput::make('service_order_id')
                    ->setLabel(__('forms.tasks.service_order'))
                    ->helperText(__('forms.tasks.service_order_helper'))
                    ->setOptions(self::serviceOrderOptions())
                    ->setRules('required|exists:service_orders,id')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.tasks.sectors'))
                    ->helperText(__('forms.tasks.sectors_helper'))
                    ->setOptions(self::sectorOptions())
                    ->setRules('required|exists:sectors,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.tasks.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.tasks.name'))
                    ->helperText(__('forms.tasks.name_helper'))
                    ->setRules('sometimes|string|max:150')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tasks.description'))
                    ->helperText(__('forms.tasks.description_helper'))
                    ->setRows(3)
                    ->setRules('nullable|string|max:1000')
            )
            ->field(
                SelectInput::make('service_order_id')
                    ->setLabel(__('forms.tasks.service_order'))
                    ->helperText(__('forms.tasks.service_order_helper'))
                    ->setOptions(self::serviceOrderOptions())
                    ->setRules('sometimes|exists:service_orders,id')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.tasks.sectors'))
                    ->helperText(__('forms.tasks.sectors_helper'))
                    ->setOptions(self::sectorOptions())
                    ->setRules('sometimes|exists:sectors,id')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.tasks.status'))
                    ->helperText(__('forms.tasks.status_helper'))
                    ->setOptions(TaskStatus::options())
                    ->setRules('sometimes|string')
            );
    }

    private static function serviceOrderOptions(): array
    {
        return ServiceOrder::whereNotIn('status', [
                ServiceOrderStatus::COMPLETED->value,
                ServiceOrderStatus::CANCELLED->value,
            ])
            ->orderBy('process')
            ->get(['id', 'process'])
            ->map(fn($so) => ['value' => $so->id, 'label' => $so->process])
            ->toArray();
    }

    private static function sectorOptions(): array
    {
        return Sector::orderBy('name')->get(['id', 'name'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();
    }
}
