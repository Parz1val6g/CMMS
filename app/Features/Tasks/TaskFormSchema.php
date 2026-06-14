<?php

namespace App\Features\Tasks;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextAreaInput, SelectInput, DateRangeInput};
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Core\Cache\RefCache;

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
                    ->setRules('string|max:1000')
            )
            ->field(
                SelectInput::make('service_order_id')
                    ->setLabel(__('forms.tasks.service_order'))
                    ->setRequired()
                    ->helperText(__('forms.tasks.service_order_helper'))
                    ->setOptions(self::serviceOrderOptions())
                    ->setRules('exists:service_orders,id')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.tasks.sectors'))
                    ->setRequired()
                    ->helperText(__('forms.tasks.sectors_helper'))
                    ->setOptions(self::sectorOptions())
                    ->setRules('exists:sectors,id')
            )
            ->field(
                DateRangeInput::make('date_range')
                    ->setLabel(__('forms.tasks.date_range'))
                    ->setRequired()
                    ->setStartName('start_date')
                    ->setEndName('end_date')
                    ->setRules('date')
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
                DateRangeInput::make('date_range')
                    ->setLabel(__('forms.tasks.date_range'))
                    ->setStartName('start_date')
                    ->setEndName('end_date')
                    ->setRules('sometimes|date')
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
        return RefCache::sectors();
    }
}
