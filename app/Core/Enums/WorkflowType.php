<?php

namespace App\Core\Enums;

/**
 * @deprecated Kept for backward compatibility with legacy records.
 *             Do NOT use in new code. The LOAN case is being replaced by
 *             the LoanOrders feature module. The cast from ServiceOrder
 *             model has been removed — workflow_type is now a raw string.
 */
enum WorkflowType: string
{
    case STANDARD = 'regular';
    case LOAN = 'loan';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => __('enums.workflow_type.regular'),
            self::LOAN => __('enums.workflow_type.loan'),
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
