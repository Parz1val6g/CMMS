<?php

namespace App\Features\MiniTasks;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextAreaInput, SelectInput, TextInput};
use App\Features\Equipments\Models\Equipment;
use App\Features\Materials\Models\Material;
use App\Features\Tasks\Models\Task;
use App\Features\Workers\Models\Worker;
use App\Features\Teams\Models\Team;

class MiniTaskFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.mini_tasks.create_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.mini_tasks.description'))
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                SelectInput::make('task_id')
                    ->setLabel(__('forms.mini_tasks.task'))
                    ->setOptions(self::taskOptions())
                    ->setRules('required|exists:tasks,id')
            )
            ->field(
                TextInput::make('start_date')
                    ->setLabel(__('forms.mini_tasks.start_date'))
                    ->setType('date')
                    ->setRequired()
                    ->setRules('required|date')
            )
            ->field(
                TextInput::make('end_date')
                    ->setLabel(__('forms.mini_tasks.end_date'))
                    ->setType('date')
                    ->setRequired()
                    ->setRules('required|date|after_or_equal:start_date')
            )
            ->field(
                SelectInput::make('worker_ids')
                    ->setLabel(__('forms.mini_tasks.workers'))
                    ->setOptions(self::workerOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('team_ids')
                    ->setLabel(__('forms.mini_tasks.teams'))
                    ->setOptions(self::teamOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('material_ids')
                    ->setLabel(__('forms.mini_tasks.material_ids'))
                    ->setOptions(self::materialOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('equipment_ids')
                    ->setLabel(__('forms.mini_tasks.equipment_ids'))
                    ->setOptions(self::equipmentOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.mini_tasks.edit_title'))
            ->field(
                TextAreaInput::make('description')
                    ->setLabel(__('forms.mini_tasks.description'))
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                SelectInput::make('task_id')
                    ->setLabel(__('forms.mini_tasks.task'))
                    ->setOptions(self::taskOptions())
                    ->setRules('sometimes|exists:tasks,id')
            )
            ->field(
                TextInput::make('start_date')
                    ->setLabel(__('forms.mini_tasks.start_date'))
                    ->setType('date')
                    ->setRules('sometimes|nullable|date')
            )
            ->field(
                TextInput::make('end_date')
                    ->setLabel(__('forms.mini_tasks.end_date'))
                    ->setType('date')
                    ->setRules('sometimes|nullable|date|after_or_equal:start_date')
            )
            ->field(
                SelectInput::make('worker_ids')
                    ->setLabel(__('forms.mini_tasks.workers'))
                    ->setOptions(self::workerOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('team_ids')
                    ->setLabel(__('forms.mini_tasks.teams'))
                    ->setOptions(self::teamOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('material_ids')
                    ->setLabel(__('forms.mini_tasks.material_ids'))
                    ->setOptions(self::materialOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            )
            ->field(
                SelectInput::make('equipment_ids')
                    ->setLabel(__('forms.mini_tasks.equipment_ids'))
                    ->setOptions(self::equipmentOptions())
                    ->multiple()
                    ->setRules('nullable|array')
            );
    }

    private static function taskOptions(): array
    {
        return Task::whereNotIn('status', [
                TaskStatus::COMPLETED->value,
                TaskStatus::CANCELLED->value,
            ])
            ->orderBy('reference')
            ->get(['id', 'reference', 'description'])
            ->map(fn($t) => [
                'value' => $t->id,
                'label' => $t->reference . ($t->description ? ' — ' . \Str::limit($t->description, 50) : ''),
            ])
            ->toArray();
    }

    private static function workerOptions(): array
    {
        return Worker::join('users', 'users.id', '=', 'workers.user_id')
            ->orderBy('users.first_name')
            ->get(['workers.id', 'workers.team_id', 'users.first_name', 'users.last_name'])
            ->map(fn($w) => [
                'value'   => $w->id,
                'label'   => trim("{$w->first_name} {$w->last_name}"),
                'team_id' => $w->team_id,
            ])
            ->toArray();
    }

    private static function teamOptions(): array
    {
        return Team::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
            ->toArray();
    }

    public static function materialOptions(): array
    {
        return Material::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($m) => ['value' => $m->id, 'label' => $m->name])
            ->toArray();
    }

    public static function equipmentOptions(): array
    {
        return Equipment::whereNotIn('status', [
                EquipmentStatus::RETIRED->value,
                EquipmentStatus::INACTIVE->value,
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($e) => ['value' => $e->id, 'label' => $e->name])
            ->toArray();
    }
}
