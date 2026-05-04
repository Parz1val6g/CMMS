<?php

namespace App\Features\Clients\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, EmailInput};

class ClientFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->setRequired()
                    ->helperText('Portuguese Fiscal Identification Number (9 digits)')
                    ->helpExamples(['123456789', '987654321'])
                    ->setRules('required|string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->setRequired()
                    ->helperText('Enter the client\'s first name')
                    ->setRules('required|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Last Name')
                    ->setRequired()
                    ->helperText('Enter the client\'s last name')
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
                    ->helperText('Phone number with country code (optional)')
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->helperText('Portuguese Fiscal Identification Number')
                    ->helpExamples(['123456789', '987654321'])
                    ->setRules('sometimes|string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->helperText('Enter the client\'s first name')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Last Name')
                    ->helperText('Enter the client\'s last name')
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
                    ->helperText('Phone number with country code (optional)')
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('nullable|string|max:20')
            );
    }
}
