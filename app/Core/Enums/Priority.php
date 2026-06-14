<?php

namespace App\Core\Enums;

enum Priority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => __('enums.priority.low'),
            self::NORMAL => __('enums.priority.normal'),
            self::HIGH => __('enums.priority.high'),
            self::URGENT => __('enums.priority.urgent'),
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
