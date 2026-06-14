<?php

namespace App\Core\Enums;

enum TicketPriority: string
{
    case LOW    = 'low';
    case NORMAL = 'normal';
    case HIGH   = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW    => __('enums.ticket_priority.low'),
            self::NORMAL => __('enums.ticket_priority.normal'),
            self::HIGH   => __('enums.ticket_priority.high'),
            self::URGENT => __('enums.ticket_priority.urgent'),
        };
    }

    public static function sortOrder(): array
    {
        return ['low', 'normal', 'high', 'urgent'];
    }

    public static function options(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
