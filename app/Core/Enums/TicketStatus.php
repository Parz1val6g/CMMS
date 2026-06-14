<?php

namespace App\Core\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case CONVERTED = 'converted';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OPEN        => __('enums.ticket_status.open'),
            self::IN_PROGRESS => __('enums.ticket_status.in_progress'),
            self::CONVERTED   => __('enums.ticket_status.converted'),
            self::CANCELLED   => __('enums.ticket_status.cancelled'),
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CONVERTED, self::CANCELLED]);
    }

    public static function sortOrder(): array
    {
        return ['open', 'in_progress', 'converted', 'cancelled'];
    }

    public static function options(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
