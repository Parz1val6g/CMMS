<?php

namespace App\Features\Tasks;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextAreaInput, SelectInput, DateRangeInput};
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Sectors\Models\Sector;

class TaskFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.tasks.create_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tasks.description'))
                    ->setRequired()
                    ->helperText(__('forms.tasks.description_helper'))
                    ->setRows(3)
                    ->setRules('required|string|max:1000')
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
            )
            ->field(
                DateRangeInput::make('start_date')
                    ->setLabel(__('forms.tasks.start_date'))
                    ->setRequired()
                    ->setDateMode('single')
                    ->setRules('required|date')
            )
            ->field(
                DateRangeInput::make('end_date')
                    ->setLabel(__('forms.tasks.end_date'))
                    ->setRequired()
                    ->setDateMode('single')
                    ->setRules('required|date|after_or_equal:start_date')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.tasks.edit_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.tasks.description'))
                    ->helperText(__('forms.tasks.description_helper'))
                    ->setRows(3)
                    ->setRules('sometimes|string|max:1000')
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
            )
            ->field(
                DateRangeInput::make('start_date')
                    ->setLabel(__('forms.tasks.start_date'))
                    ->setDateMode('single')
                    ->setRules('sometimes|date')
            )
            ->field(
                DateRangeInput::make('end_date')
                    ->setLabel(__('forms.tasks.end_date'))
                    ->setDateMode('single')
                    ->setRules('sometimes|date|after_or_equal:start_date')
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
