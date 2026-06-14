<?php

namespace App\Features\Clients;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, EmailInput};

class ClientFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.clients.create_title'))
            ->field(
                TextInput::make('nif')
                    ->setLabel(__('forms.clients.nif'))
                    ->setRequired()
                    ->helperText(__('forms.clients.nif_helper'))
                    ->helpExamples(['123456789', '987654321'])
                    ->setRules('string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel(__('forms.clients.first_name'))
                    ->setRequired()
                    ->helperText(__('forms.clients.first_name_helper'))
                    ->setRules('string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel(__('forms.clients.last_name'))
                    ->setRequired()
                    ->helperText(__('forms.clients.last_name_helper'))
                    ->setRules('string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel(__('forms.clients.email'))
                    ->setRequired()
                    ->setRules('email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.clients.phone'))
                    ->setRequired()
                    ->setPlaceholder(__('forms.clients.phone_placeholder'))
                    ->helperText(__('forms.clients.phone_helper'))
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('string|max:20')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.clients.edit_title'))
            ->field(
                TextInput::make('nif')
                    ->setLabel(__('forms.clients.nif'))
                    ->helperText(__('forms.clients.nif_helper'))
                    ->helpExamples(['123456789', '987654321'])
                    ->setRules('sometimes|string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel(__('forms.clients.first_name'))
                    ->helperText(__('forms.clients.first_name_helper'))
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel(__('forms.clients.last_name'))
                    ->helperText(__('forms.clients.last_name_helper'))
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel(__('forms.clients.email'))
                    ->setRules('sometimes|email|max:255|unique:users,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel(__('forms.clients.phone'))
                    ->setPlaceholder(__('forms.clients.phone_placeholder'))
                    ->helperText(__('forms.clients.phone_helper'))
                    ->helpExamples(['+351 910 000 000', '+351 212 345 678'])
                    ->setRules('string|max:20')
            );
    }
}
