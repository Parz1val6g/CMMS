<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\LoanOrderStatus;
use Tests\TestCase;

class LoanOrderStatusTest extends TestCase
{
    public function test_cases_have_correct_values(): void
    {
        $this->assertSame('pending', LoanOrderStatus::PENDING->value);
        $this->assertSame('checked_out', LoanOrderStatus::CHECKED_OUT->value);
        $this->assertSame('returned', LoanOrderStatus::RETURNED->value);
        $this->assertSame('cancelled', LoanOrderStatus::CANCELLED->value);
    }

    public function test_isTerminal(): void
    {
        $this->assertFalse(LoanOrderStatus::PENDING->isTerminal());
        $this->assertFalse(LoanOrderStatus::CHECKED_OUT->isTerminal());
        $this->assertTrue(LoanOrderStatus::RETURNED->isTerminal());
        $this->assertTrue(LoanOrderStatus::CANCELLED->isTerminal());
    }

    public function test_isOperational(): void
    {
        $this->assertTrue(LoanOrderStatus::PENDING->isOperational());
        $this->assertTrue(LoanOrderStatus::CHECKED_OUT->isOperational());
        $this->assertFalse(LoanOrderStatus::RETURNED->isOperational());
        $this->assertFalse(LoanOrderStatus::CANCELLED->isOperational());
    }

    public function test_label_returns_string_for_each_case(): void
    {
        foreach (LoanOrderStatus::cases() as $case) {
            $this->assertIsString($case->label());
            $this->assertNotEmpty($case->label());
        }
    }

    public function test_options_returns_array_of_value_label_pairs(): void
    {
        $options = LoanOrderStatus::options();

        $this->assertCount(5, $options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertContains($option['value'], ['pending', 'approved', 'checked_out', 'returned', 'cancelled']);
        }
    }
}
