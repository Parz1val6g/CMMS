<?php

namespace App\Features\Workers;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, EmailInput, NumberInput};
use App\Features\Teams\Models\Team;

class WorkerFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.workers.create_title'))
            ->field(
                TextInput::make('first_name')
                    ->setLabel(__('forms.workers.first_name'))
                    ->setRequired()
                    ->helperText(__('forms.workers.first_name_helper'))
                    ->setRules('required|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel(__('forms.workers.last_name'))
                    ->setRequired()
                    ->helperText(__('forms.workers.last_name_helper'))
                    ->setRules('required|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel(__('forms.workers.email'))
                    ->setRequired()
                    ->setRules('required|email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.workers.phone'))
                    ->setPlaceholder(__('forms.workers.phone_placeholder'))
                    ->helperText(__('forms.workers.phone_helper'))
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel(__('forms.workers.team'))
                    ->helperText(__('forms.workers.team_helper'))
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            )
            ->field(
                NumberInput::make('cost_per_hour')
                    ->setLabel(__('forms.workers.cost_per_hour'))
                    ->setRequired()
                    ->step(0.01)
                    ->min(0)
                    ->setRules('required|numeric|min:0|max:9999.99')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.workers.edit_title'))
            ->field(
                TextInput::make('first_name')
                    ->setLabel(__('forms.workers.first_name'))
                    ->helperText(__('forms.workers.first_name_helper'))
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel(__('forms.workers.last_name'))
                    ->helperText(__('forms.workers.last_name_helper'))
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel(__('forms.workers.email'))
                    ->setRules('sometimes|email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.workers.phone'))
                    ->setPlaceholder(__('forms.workers.phone_placeholder'))
                    ->helperText(__('forms.workers.phone_helper'))
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel(__('forms.workers.team'))
                    ->helperText(__('forms.workers.team_helper'))
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            )
            ->field(
                NumberInput::make('cost_per_hour')
                    ->setLabel(__('forms.workers.cost_per_hour'))
                    ->step(0.01)
                    ->min(0)
                    ->setRules('sometimes|numeric|min:0|max:9999.99')
            );
    }

    private static function teamOptions(): array
    {
        return Team::orderBy('name')->get(['id', 'name'])
            ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
            ->toArray();
    }
}
