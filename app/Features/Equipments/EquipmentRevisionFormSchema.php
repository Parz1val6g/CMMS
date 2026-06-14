<?php

namespace App\Features\Equipments;

use App\Core\Enums\EquipmentRevisionStatus;
use App\Core\Forms\FormSchema;
use App\Core\Forms\Fields\TextInput;
use App\Core\Forms\Fields\TextAreaInput;
use App\Core\Forms\Fields\SelectInput;
use App\Core\Forms\Fields\DateTimeInput;

class EquipmentRevisionFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.equipment_revisions.create_title'))
            ->field(
                SelectInput::make('equipment_id')
                    ->setLabel(__('forms.equipment_revisions.equipment'))
                    ->setRequired()
                    ->setRules('uuid|exists:equipments,id')
            )
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.equipment_revisions.status'))
                    ->setRequired()
                    ->setOptions(array_map(
                        fn(EquipmentRevisionStatus $c) => ['value' => $c->value, 'label' => $c->label()],
                        EquipmentRevisionStatus::cases()
                    ))
                    ->setRules('string|in:' . implode(',', array_map(fn($c) => $c->value, EquipmentRevisionStatus::cases())))
            )
            ->field(
                DateTimeInput::make('revision_date')
                    ->setLabel(__('forms.equipment_revisions.revision_date'))
                    ->setRequired()
                    ->setRules('date')
            )
            ->field(
                TextAreaInput::make('notes')
                    ->setLabel(__('forms.equipment_revisions.notes'))
                    ->setRows(3)
                    ->setRules('nullable|string')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make(__('forms.equipment_revisions.edit_title'))
            ->field(
                SelectInput::make('status')
                    ->setLabel(__('forms.equipment_revisions.status'))
                    ->setOptions(array_map(
                        fn(EquipmentRevisionStatus $c) => ['value' => $c->value, 'label' => $c->label()],
                        EquipmentRevisionStatus::cases()
                    ))
                    ->setRules('sometimes|string|in:' . implode(',', array_map(fn($c) => $c->value, EquipmentRevisionStatus::cases())))
            )
            ->field(
                DateTimeInput::make('revision_date')
                    ->setLabel(__('forms.equipment_revisions.revision_date'))
                    ->setRules('sometimes|date')
            )
            ->field(
                TextAreaInput::make('notes')
                    ->setLabel(__('forms.equipment_revisions.notes'))
                    ->setRows(3)
                    ->setRules('nullable|string')
            );
    }
}
