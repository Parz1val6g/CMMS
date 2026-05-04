<?php

namespace App\Features\Sectors\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput};
use App\Shared\Models\User;

class SectorFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Sector')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('head_id')
                    ->setLabel('Head')
                    ->setRequired()
                    ->setOptions(self::userOptions())
                    ->setRules('required|exists:users,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Sector')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('head_id')
                    ->setLabel('Head')
                    ->setOptions(self::userOptions())
                    ->setRules('nullable|exists:users,id')
            );
    }

    private static function userOptions(): array
    {
        return User::all()
            ->map(fn($u) => [
                'value' => $u->id,
                'label' => $u->first_name . ' ' . $u->last_name,
            ])
            ->toArray();
    }
}
