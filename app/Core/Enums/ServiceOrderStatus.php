<?php
namespace App\Core\Enums;
enum ServiceOrderStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.service_order_status.pending'),
            self::IN_PROGRESS => __('enums.service_order_status.in_progress'),
            self::AWAITING_APPROVAL => __('enums.service_order_status.awaiting_approval'),
            self::COMPLETED => __('enums.service_order_status.completed'),
            self::CANCELLED => __('enums.service_order_status.cancelled'),
        };
    }
    public static function sortOrder(): array
    {
        return ['pending', 'in_progress', 'awaiting_approval', 'completed', 'cancelled'];
    }

    public static function options(): array
    {
        return array_map(fn(self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}