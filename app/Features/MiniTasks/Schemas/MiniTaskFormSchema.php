<?php

namespace App\Features\MiniTasks\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextAreaInput, SelectInput};
use App\Features\Tasks\Models\Task;
use App\Features\Workers\Models\Worker;
use App\Features\Teams\Models\Team;

class MiniTaskFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Nova Mini-Tarefa')
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                SelectInput::make('task_id')
                    ->setLabel('Task')
                    ->setOptions(self::taskOptions())
                    ->setRules('required|exists:tasks,id')
            )
            ->field(
                SelectInput::make('worker_ids')
                    ->setLabel('Workers')
                    ->setOptions(self::workerOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('team_ids')
                    ->setLabel('Teams')
                    ->setOptions(self::teamOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Mini-Tarefa')
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                SelectInput::make('task_id')
                    ->setLabel('Task')
                    ->setOptions(self::taskOptions())
                    ->setRules('sometimes|exists:tasks,id')
            )
            ->field(
                SelectInput::make('worker_ids')
                    ->setLabel('Workers')
                    ->setOptions(self::workerOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('team_ids')
                    ->setLabel('Teams')
                    ->setOptions(self::teamOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            );
    }

    private static function taskOptions(): array
    {
        return Task::orderBy('name')
            ->get()
            ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
            ->toArray();
    }

    private static function workerOptions(): array
    {
        return Worker::with('user')
            ->get()
            ->map(fn($w) => [
                'value' => $w->id,
                'label' => ($w->user?->first_name ?? '') . ' ' . ($w->user?->last_name ?? ''),
            ])
            ->toArray();
    }

    private static function teamOptions(): array
    {
        return Team::orderBy('name')
            ->get()
            ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
            ->toArray();
    }
}
