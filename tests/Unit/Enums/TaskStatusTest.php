<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\TaskStatus;
use Tests\TestCase;

class TaskStatusTest extends TestCase
{
    public function test_awaiting_approval_has_correct_value(): void
    {
        $this->assertSame('awaiting_approval', TaskStatus::AWAITING_APPROVAL->value);
    }

    public function test_awaiting_approval_label_returns_non_empty_string(): void
    {
        $label = TaskStatus::AWAITING_APPROVAL->label();

        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_awaiting_approval_is_open(): void
    {
        $this->assertTrue(TaskStatus::AWAITING_APPROVAL->isOpen());
        $this->assertFalse(TaskStatus::AWAITING_APPROVAL->isClosed());
    }

    public function test_all_cases_have_non_empty_labels(): void
    {
        foreach (TaskStatus::cases() as $case) {
            $this->assertNotEmpty($case->label(), "label() vazio para {$case->name}");
        }
    }
}
