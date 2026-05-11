<?php

namespace App\Core\Enums;

enum EquipmentStatus: string
{
    case ACTIVE = 'active';
    case IN_USE = 'in_use';
    case MAINTENANCE_PENDING = 'maintenance_pending';
    case UNDER_MAINTENANCE = 'under_maintenance';
    case BROKEN = 'broken';
    case UNDER_REPAIR = 'under_repair';
    case RESERVED = 'reserved';
    case INACTIVE = 'inactive';
    case RETIRED = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('enums.equipment_status.active'),
            self::IN_USE => __('enums.equipment_status.in_use'),
            self::MAINTENANCE_PENDING => __('enums.equipment_status.maintenance_pending'),
            self::UNDER_MAINTENANCE => __('enums.equipment_status.under_maintenance'),
            self::BROKEN => __('enums.equipment_status.broken'),
            self::UNDER_REPAIR => __('enums.equipment_status.under_repair'),
            self::RESERVED => __('enums.equipment_status.reserved'),
            self::INACTIVE => __('enums.equipment_status.inactive'),
            self::RETIRED => __('enums.equipment_status.retired'),
        };
    }

    /**
     * States that allow a new loan to be created (active only).
     */
    public function isAvailableForLoan(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * States considered "operational" (can be used normally).
     */
    public function isOperational(): bool
    {
        return in_array($this, [self::ACTIVE, self::IN_USE, self::RESERVED]);
    }

    /**
     * States considered "stopped" (cannot be used).
     */
    public function isStopped(): bool
    {
        return in_array($this, [self::MAINTENANCE_PENDING, self::UNDER_MAINTENANCE,
            self::BROKEN, self::UNDER_REPAIR, self::INACTIVE, self::RETIRED]);
    }

    public static function options(): array
    {
        return array_map(
            fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
