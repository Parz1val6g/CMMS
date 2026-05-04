<?php

namespace App\Core\Enums;

enum WorkflowType: string
{
    case STANDARD = 'regular';
    case LOAN = 'loan';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard',
            self::LOAN => 'Loan',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
