<?php

namespace App\Features\Sectors;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput};
use App\Shared\Models\User;

class SectorFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.sectors.create_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.sectors.name'))
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                SelectInput::make('head_id')
                    ->setLabel(__('forms.sectors.head'))
                    ->setRequired()
                    ->setOptions(self::userOptions())
                    ->setRules('required|exists:users,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.sectors.edit_title'))
            ->field(
                TextInput::make('name')
                    ->setLabel(__('forms.sectors.name'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                SelectInput::make('head_id')
                    ->setLabel(__('forms.sectors.head'))
                    ->setOptions(self::userOptions())
                    ->setRules('nullable|exists:users,id')
            );
    }

    private static function userOptions(): array
    {
        return User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager', 'task_manager']))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn($u) => [
                'value' => $u->id,
                'label' => $u->first_name . ' ' . $u->last_name,
            ])
            ->toArray();
    }
}
