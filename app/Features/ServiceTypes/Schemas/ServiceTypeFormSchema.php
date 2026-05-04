<?php

namespace App\Features\ServiceTypes\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, TextAreaInput};

class ServiceTypeFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Tipo de Serviço')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRequired()
                    ->setRules('required|string|max:250')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Tipo de Serviço')
            ->field(
                TextInput::make('name')
                    ->setLabel('Name')
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextAreaInput::make('description')
                    ->setLabel('Description')
                    ->setRows(3)
                    ->setRules('sometimes|string|max:250')
            );
    }
}
