<?php

namespace App\Features\Teams\Schemas;

use App\Features\Sectors\Models\Sector;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput};

class TeamFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Nova Equipa')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel('Sector')
                    ->setOptions(self::sectorOptions())
                    ->setRules('required|exists:sectors,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Equipa')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('sector_id')
                    ->setLabel('Sector')
                    ->setOptions(self::sectorOptions())
                    ->setRules('sometimes|exists:sectors,id')
            );
    }

    private static function sectorOptions(): array
    {
        return Sector::all()
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();
    }
}
