<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\WorkflowType;
use Tests\TestCase;

class WorkflowTypeSafetyTest extends TestCase
{
    public function test_workflow_type_enum_still_exists_for_backward_compatibility(): void
    {
        // The enum should NOT be deleted — just the cast removed
        // This ensures code that still references WorkflowType::LOAN->value works
        $this->assertEquals('loan', WorkflowType::LOAN->value);
        $this->assertEquals('regular', WorkflowType::STANDARD->value);
    }
}
