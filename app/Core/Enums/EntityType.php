<?php

namespace App\Core\Enums;

enum EntityType: string
{
    case MUNICIPAL_COUNCIL = 'municipal_council';
    case PARISH_COUNCIL    = 'parish_council';
    case OTHER             = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MUNICIPAL_COUNCIL => __('enums.entity_type.municipal_council'),
            self::PARISH_COUNCIL    => __('enums.entity_type.parish_council'),
            self::OTHER             => __('enums.entity_type.other'),
        };
    }

    public static function options(): array
    {
        return array_map(fn(self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
