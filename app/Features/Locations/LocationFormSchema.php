<?php

namespace App\Features\Locations;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, MapInput};

class LocationFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.locations.create_title'))
            ->field(
                TextInput::make('street_address')
                    ->setLabel(__('forms.locations.street_address'))
                    ->setRequired()
                    ->setRules('required|string|max:100')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.locations.postal_code'))
                    ->setRequired()
                    ->setRules('required|string|max:8')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.locations.parish'))
                    ->setOptions([])
                    ->setRules('required|exists:parishes,id')
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel(__('forms.locations.landmark'))
                    ->setRules('nullable|string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.locations.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.locations.edit_title'))
            ->field(
                TextInput::make('street_address')
                    ->setLabel(__('forms.locations.street_address'))
                    ->setRules('sometimes|string|max:100')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.locations.postal_code'))
                    ->setRules('sometimes|string|max:8')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.locations.parish'))
                    ->setOptions([])
                    ->setRules('sometimes|exists:parishes,id')
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel(__('forms.locations.landmark'))
                    ->setRules('nullable|string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.locations.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('nullable|numeric|between:-90,90')
            );
    }
}
