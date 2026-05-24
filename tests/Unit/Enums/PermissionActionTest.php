<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\PermissionAction;
use Tests\TestCase;

class PermissionActionTest extends TestCase
{
    public function test_cases_returns_20_cases(): void
    {
        $this->assertCount(20, PermissionAction::cases());
    }

    public function test_all_cases_have_non_empty_labels(): void
    {
        foreach (PermissionAction::cases() as $case) {
            $this->assertNotEmpty($case->label(), "label() empty for {$case->name}");
        }
    }

    public function test_new_actions_exist(): void
    {
        $this->assertSame('cancel', PermissionAction::CANCEL->value);
        $this->assertSame('complete', PermissionAction::COMPLETE->value);
        $this->assertSame('reject', PermissionAction::REJECT->value);
        $this->assertSame('activate', PermissionAction::ACTIVATE->value);
        $this->assertSame('approve', PermissionAction::APPROVE->value);
        $this->assertSame('checkout', PermissionAction::CHECKOUT->value);
        $this->assertSame('convert', PermissionAction::CONVERT->value);
        $this->assertSame('initiate_return', PermissionAction::INITIATE_RETURN->value);
        $this->assertSame('assign_workers', PermissionAction::ASSIGN_WORKERS->value);
        $this->assertSame('assign_materials', PermissionAction::ASSIGN_MATERIALS->value);
        $this->assertSame('assign_equipment', PermissionAction::ASSIGN_EQUIPMENT->value);
    }
}
