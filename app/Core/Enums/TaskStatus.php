<?php

namespace App\Core\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.task_status.pending'),
            self::IN_PROGRESS => __('enums.task_status.in_progress'),
            self::AWAITING_APPROVAL => __('enums.task_status.awaiting_approval'),
            self::COMPLETED => __('enums.task_status.completed'),
            self::BLOCKED => __('enums.task_status.blocked'),
            self::CANCELLED => __('enums.task_status.cancelled'),
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS, self::BLOCKED, self::AWAITING_APPROVAL]);
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }
    public static function options(): array
    {
        return array_map(fn(self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
