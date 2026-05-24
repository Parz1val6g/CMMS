<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\ServiceOrderStatus;
use Tests\TestCase;

class ServiceOrderStatusTest extends TestCase
{
    public function test_awaiting_approval_has_correct_value(): void
    {
        $this->assertSame('awaiting_approval', ServiceOrderStatus::AWAITING_APPROVAL->value);
    }

    public function test_awaiting_approval_label_returns_non_empty_string(): void
    {
        $label = ServiceOrderStatus::AWAITING_APPROVAL->label();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_all_cases_have_non_empty_labels(): void
    {
        foreach (ServiceOrderStatus::cases() as $case) {
            $this->assertNotEmpty($case->label(), "label() vazio para {$case->name}");
        }
    }
}
