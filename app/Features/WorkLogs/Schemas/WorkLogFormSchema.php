<?php

namespace App\Features\WorkLogs\Schemas;

use App\Core\Enums\WorkLogStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput};

class WorkLogFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Registo de Trabalho')
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                SelectInput::make('mini_task_id')
                    ->setLabel('Mini-Task')
                    ->setOptions([])
                    ->setRules('required|exists:mini_tasks,id')
            )
            ->field(
                TextInput::make('started_at')
                    ->setLabel('Started At')
                    ->setType('datetime-local')
                    ->setRequired()
                    ->setRules('required|date')
            )
            ->field(
                TextInput::make('completed_at')
                    ->setLabel('Completed At')
                    ->setType('datetime-local')
                    ->setRules('nullable|date|after:started_at')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Registo de Trabalho')
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                SelectInput::make('mini_task_id')
                    ->setLabel('Mini-Task')
                    ->setOptions([])
                    ->setRules('sometimes|exists:mini_tasks,id')
            )
            ->field(
                TextInput::make('started_at')
                    ->setLabel('Started At')
                    ->setType('datetime-local')
                    ->setRules('sometimes|date')
            )
            ->field(
                TextInput::make('completed_at')
                    ->setLabel('Completed At')
                    ->setType('datetime-local')
                    ->setRules('nullable|date|after:started_at')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel('Status')
                    ->setOptions(WorkLogStatus::options())
                    ->setRules('sometimes|string')
            );
    }
}
