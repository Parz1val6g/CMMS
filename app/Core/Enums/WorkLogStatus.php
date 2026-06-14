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
            self::IN_PROGRESS => __('enums.work_log_status.in_progress'),
            self::SUBMITTED => __('enums.work_log_status.submitted'),
            self::APPROVED => __('enums.work_log_status.approved'),
            self::REJECTED => __('enums.work_log_status.rejected'),
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
    public static function sortOrder(): array
    {
        return ['in_progress', 'submitted', 'approved', 'rejected'];
    }

    public static function options(): array
    {
        return array_map(fn(self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
