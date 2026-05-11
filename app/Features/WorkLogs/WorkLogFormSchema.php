<?php

namespace App\Features\WorkLogs;

use App\Core\Enums\WorkLogStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput, SelectInput};

class WorkLogFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.work_logs.create_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.work_logs.description'))
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                SelectInput::make('mini_task_id')
                    ->setLabel(__('forms.work_logs.mini_task'))
                    ->setOptions([])
                    ->setRules('required|exists:mini_tasks,id')
            )
            ->field(
                TextInput::make('started_at')
                    ->setLabel(__('forms.work_logs.started_at'))
                    ->setType('datetime-local')
                    ->setRequired()
                    ->setRules('required|date')
            )
            ->field(
                TextInput::make('completed_at')
                    ->setLabel(__('forms.work_logs.completed_at'))
                    ->setType('datetime-local')
                    ->setRules('nullable|date|after:started_at')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.work_logs.edit_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.work_logs.description'))
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                SelectInput::make('mini_task_id')
                    ->setLabel(__('forms.work_logs.mini_task'))
                    ->setOptions([])
                    ->setRules('sometimes|exists:mini_tasks,id')
            )
            ->field(
                TextInput::make('started_at')
                    ->setLabel(__('forms.work_logs.started_at'))
                    ->setType('datetime-local')
                    ->setRules('sometimes|date')
            )
            ->field(
                TextInput::make('completed_at')
                    ->setLabel(__('forms.work_logs.completed_at'))
                    ->setType('datetime-local')
                    ->setRules('nullable|date|after:started_at')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.work_logs.status'))
                    ->setOptions(WorkLogStatus::options())
                    ->setRules('sometimes|string')
            );
    }
}
