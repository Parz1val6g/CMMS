<?php

namespace App\Core\Enums;

enum ServicesOrdersPriority: string
{
    case URGENT = 'urgent';
    case HIGH = 'high';
    case NORMAL = 'normal';
    case LOW = 'low';

    public function label(): string
    {
        return match ($this) {
            self::URGENT => 'Urgent',
            self::HIGH => 'High',
            self::NORMAL => 'Normal',
            self::LOW => 'Low',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::URGENT => 4,
            self::HIGH => 3,
            self::NORMAL => 2,
            self::LOW => 1,
        };
    }

    public function isHighPriority(): bool
    {
        return in_array($this, [self::URGENT, self::HIGH]);
    }
}
