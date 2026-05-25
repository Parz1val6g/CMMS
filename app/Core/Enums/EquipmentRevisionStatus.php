<?php

namespace App\Core\Enums;

enum EquipmentRevisionStatus: string
{
    case APPROVED = 'approved';
    case PENDING = 'pending';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::APPROVED => __('enums.equipment_revision_status.approved'),
            self::PENDING => __('enums.equipment_revision_status.pending'),
            self::REJECTED => __('enums.equipment_revision_status.rejected'),
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
