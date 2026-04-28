<?php

namespace App\Core\Enums;

enum WorkLogStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In Progress',
            self::SUBMITTED => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function isPending(): bool
    {
        return $this === self::SUBMITTED;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED]);
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::IN_PROGRESS => $target === self::SUBMITTED,
            self::SUBMITTED => in_array($target, [self::APPROVED, self::REJECTED]),
            default => false,
        };
    }
}
