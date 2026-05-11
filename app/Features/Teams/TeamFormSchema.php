<?php

namespace App\Features\Teams;

use App\Features\Sectors\Models\Sector;
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
            );
    }

    private static function sectorOptions(): array
    {
        return Sector::orderBy('name')->get(['id', 'name'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();
    }
}
