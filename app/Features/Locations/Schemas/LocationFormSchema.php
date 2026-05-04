<?php

namespace App\Features\Locations\Schemas;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, MapInput};

class LocationFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Nova Localização')
            ->field(
                TextInput::make('street_address')
                    ->setLabel('Street Address')
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel('Postal Code')
                    ->setRequired()
                    ->setRules('required|string|max:8')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel('Parish')
                    ->setOptions([])
                    ->setRules('required|exists:parishes,id')
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel('Landmark')
                    ->setRules('nullable|string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel('Coordinates')
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Localização')
            ->field(
                TextInput::make('street_address')
                    ->setLabel('Street Address')
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel('Postal Code')
                    ->setRules('sometimes|string|max:8')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel('Parish')
                    ->setOptions([])
                    ->setRules('sometimes|exists:parishes,id')
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel('Landmark')
                    ->setRules('nullable|string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel('Coordinates')
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }
}
