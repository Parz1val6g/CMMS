<?php

namespace Tests\Unit\Enums;

use App\Core\Enums\RoleName;
use Tests\TestCase;

class RoleNameTest extends TestCase
{
    public function test_has_attendant_constant(): void
    {
        $this->assertSame('attendant', RoleName::ATTENDANT);
    }

    public function test_has_ticket_manager_constant(): void
    {
        $this->assertSame('ticket_manager', RoleName::TICKET_MANAGER);
    }

    public function test_has_team_manager_constant(): void
    {
        $this->assertSame('team_manager', RoleName::TEAM_MANAGER);
    }

    public function test_existing_constants_preserved(): void
    {
        $this->assertSame('admin', RoleName::ADMIN);
        $this->assertSame('manager', RoleName::MANAGER);
        $this->assertSame('supervisor', RoleName::SUPERVISOR);
        $this->assertSame('worker', RoleName::WORKER);
        $this->assertSame('client', RoleName::CLIENT);
    }
}
