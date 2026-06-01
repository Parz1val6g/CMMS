<?php

namespace App\Features\Teams;

use App\Core\Enums\RoleName;
use App\Core\Cache\RefCache;
use App\Shared\Models\User;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput};

class TeamFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.teams.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.teams.name'))
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.teams.sector'))
                    ->setOptions(self::sectorOptions())
                    ->setRules('required|exists:sectors,id')
            )
            ->field(
                SelectInput::make('responsible_id')
                    ->setLabel(__('forms.teams.responsible'))
                    ->helperText(__('forms.teams.responsible_helper'))
                    ->setOptions(self::responsibleOptions())
                    ->setRules('required|exists:users,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.teams.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.teams.name'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel(__('forms.teams.sector'))
                    ->setOptions(self::sectorOptions())
                    ->setRules('sometimes|exists:sectors,id')
            )
            ->field(
                SelectInput::make('responsible_id')
                    ->setLabel(__('forms.teams.responsible'))
                    ->helperText(__('forms.teams.responsible_helper'))
                    ->setOptions(self::responsibleOptions())
                    ->setRules('sometimes|exists:users,id')
            );
    }

    private static function sectorOptions(): array
    {
        return RefCache::sectors();
    }

    private static function responsibleOptions(): array
    {
        $excludedRoles = [RoleName::WORKER, RoleName::CLIENT];

        return User::whereHas('roles', function ($query) use ($excludedRoles) {
            $query->whereNotIn('name', $excludedRoles);
        })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($u) => ['value' => $u->id, 'label' => $u->first_name . ' ' . $u->last_name])
            ->toArray();
    }
}
