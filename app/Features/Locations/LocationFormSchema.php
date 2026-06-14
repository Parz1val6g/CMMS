<?php

namespace App\Features\Locations;

use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\{TextInput, SelectInput, MapInput};
use App\Core\Cache\RefCache;
use App\Core\LocationCascadeOptions;

class LocationFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.locations.create_title'))
            ->field(
                TextInput::make('street_address')
                    ->setLabel(__('forms.locations.street_address'))
                    ->setRequired()
                    ->setRules('string|max:100')
            )
            ->field(
                TextInput::make('postal_code')
                    ->setLabel(__('forms.locations.postal_code'))
                    ->setRequired()
                    ->setRules('string|max:8')
            )
            ->field(
                SelectInput::make('parish_id')
                    ->setLabel(__('forms.locations.parish'))
                    ->setOptions(RefCache::parishes())
                    ->setRequired()
                    ->setRules('exists:parishes,id')
                    ->meta('useCascade', true)
                    ->meta('districts', LocationCascadeOptions::all()['districts'])
                    ->meta('municipalities', LocationCascadeOptions::all()['municipalities'])
                    ->meta('parishes', LocationCascadeOptions::all()['parishes'])
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel(__('forms.locations.landmark'))
                    ->setRules('string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.locations.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('numeric|between:-90,90')
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
                    ->setOptions(RefCache::parishes())
                    ->setRules('sometimes|exists:parishes,id')
                    ->meta('useCascade', true)
                    ->meta('districts', LocationCascadeOptions::all()['districts'])
                    ->meta('municipalities', LocationCascadeOptions::all()['municipalities'])
                    ->meta('parishes', LocationCascadeOptions::all()['parishes'])
            )
            ->field(
                TextInput::make('landmark')
                    ->setLabel(__('forms.locations.landmark'))
                    ->setRules('string|max:100')
            )
            ->field(
                MapInput::make('location')
                    ->setLabel(__('forms.locations.coordinates'))
                    ->coordinates('latitude', 'longitude')
                    ->setRules('numeric|between:-90,90')
            );
    }
}
