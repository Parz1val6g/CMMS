<?php

namespace App\Features\Workers\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, EmailInput};
use App\Features\Teams\Models\Team;

class WorkerFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Trabalhador')
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->setRequired()
                    ->helperText('Enter the worker\'s first name')
                    ->setRules('required|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Last Name')
                    ->setRequired()
                    ->helperText('Enter the worker\'s last name')
                    ->setRules('required|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setRequired()
                    ->setRules('required|email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Phone')
                    ->setPlaceholder('+351 910 000 000')
                    ->helperText('Worker\'s contact phone number (optional)')
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel('Team')
                    ->helperText('Assign the worker to a team (optional)')
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Trabalhador')
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->helperText('Enter the worker\'s first name')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Last Name')
                    ->helperText('Enter the worker\'s last name')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setRules('sometimes|email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Phone')
                    ->setPlaceholder('+351 910 000 000')
                    ->helperText('Worker\'s contact phone number (optional)')
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel('Team')
                    ->helperText('Assign the worker to a team (optional)')
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            );
    }

    private static function teamOptions(): array
    {
        return Team::all()
            ->map(fn($t) => ['value' => $t->id, 'label' => $t->name])
            ->toArray();
    }
}
