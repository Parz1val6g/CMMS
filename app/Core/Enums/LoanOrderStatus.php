<?php

namespace App\Core\Enums;

enum LoanOrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CHECKED_OUT = 'checked_out';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING     => __('enums.loan_order_status.pending'),
            self::APPROVED    => __('enums.loan_order_status.approved'),
            self::CHECKED_OUT => __('enums.loan_order_status.checked_out'),
            self::RETURNED    => __('enums.loan_order_status.returned'),
            self::CANCELLED   => __('enums.loan_order_status.cancelled'),
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::RETURNED, self::CANCELLED], true);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::APPROVED, self::CHECKED_OUT], true);
    }

    public function isOperational(): bool
    {
        return !$this->isTerminal();
    }

    public static function sortOrder(): array
    {
        return ['pending', 'approved', 'checked_out', 'returned', 'cancelled'];
    }

    public static function options(): array
    {
        return array_map(fn(self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
