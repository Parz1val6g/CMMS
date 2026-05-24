<?php

namespace Tests\Feature\Seeders;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Enums\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('role_permissions')->delete();
        DB::table('roles')->delete();

        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_attendant_role_exists(): void
    {
        $this->assertDatabaseHas('roles', ['name' => RoleName::ATTENDANT]);
    }

    public function test_attendant_can_view_service_orders(): void
    {
        $roleId = DB::table('roles')->where('name', RoleName::ATTENDANT)->value('id');

        $this->assertDatabaseHas('role_permissions', [
            'role_id'  => $roleId,
            'resource' => PermissionResource::SERVICE_ORDERS->value,
            'action'   => PermissionAction::VIEW->value,
        ]);
    }

    public function test_attendant_can_create_service_orders(): void
    {
        $roleId = DB::table('roles')->where('name', RoleName::ATTENDANT)->value('id');

        $this->assertDatabaseHas('role_permissions', [
            'role_id'  => $roleId,
            'resource' => PermissionResource::SERVICE_ORDERS->value,
            'action'   => PermissionAction::CREATE->value,
        ]);
    }

    public function test_attendant_can_view_profile(): void
    {
        $roleId = DB::table('roles')->where('name', RoleName::ATTENDANT)->value('id');

        $this->assertDatabaseHas('role_permissions', [
            'role_id'  => $roleId,
            'resource' => PermissionResource::PROFILE->value,
            'action'   => PermissionAction::VIEW->value,
        ]);
    }

    public function test_admin_has_all_20_actions(): void
    {
        $roleId = DB::table('roles')->where('name', 'admin')->value('id');
        $this->assertNotNull($roleId, 'Admin role not found');

        $permissions = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->get();

        $actionsByResource = [];
        foreach ($permissions as $perm) {
            $actionsByResource[$perm->resource][] = $perm->action;
        }

        $allActions = array_map(fn($a) => $a->value, PermissionAction::cases());

        foreach ($actionsByResource as $resource => $actions) {
            $missing = array_diff($allActions, $actions);
            $this->assertEmpty($missing, "Admin resource '{$resource}' missing actions: " . implode(', ', $missing));
        }
    }

    private function roleHasPermission(string $roleName, PermissionResource $resource, PermissionAction $action): void
    {
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');

        $this->assertNotNull($roleId, "Role '{$roleName}' not found");

        $exists = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('resource', $resource->value)
            ->where('action', $action->value)
            ->exists();

        $this->assertTrue($exists, "Role '{$roleName}' missing {$resource->value}:{$action->value}");
    }

    #[DataProvider('manager_extra_permissions')]
    public function test_manager_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('manager', $resource, $action);
    }

    public static function manager_extra_permissions(): array
    {
        return [
            [PermissionResource::SERVICE_ORDERS, PermissionAction::ACTIVATE],
            [PermissionResource::SERVICE_ORDERS, PermissionAction::COMPLETE],
            [PermissionResource::SERVICE_ORDERS, PermissionAction::CANCEL],
            [PermissionResource::TASKS, PermissionAction::CANCEL],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_WORKERS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_MATERIALS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_EQUIPMENT],
            [PermissionResource::LOAN_ORDERS, PermissionAction::APPROVE],
            [PermissionResource::LOAN_ORDERS, PermissionAction::CHECKOUT],
            [PermissionResource::LOAN_ORDERS, PermissionAction::CANCEL],
            [PermissionResource::LOAN_ORDERS, PermissionAction::COMPLETE],
            [PermissionResource::LOAN_ORDERS, PermissionAction::INITIATE_RETURN],
            [PermissionResource::WORK_LOGS, PermissionAction::COMPLETE],
            [PermissionResource::WORK_LOGS, PermissionAction::APPROVE],
            [PermissionResource::WORK_LOGS, PermissionAction::REJECT],
        ];
    }

    #[DataProvider('supervisor_extra_permissions')]
    public function test_supervisor_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('supervisor', $resource, $action);
    }

    public static function supervisor_extra_permissions(): array
    {
        return [
            [PermissionResource::SERVICE_ORDERS, PermissionAction::COMPLETE],
            [PermissionResource::TASKS, PermissionAction::COMPLETE],
            [PermissionResource::TASKS, PermissionAction::CANCEL],
            [PermissionResource::TASKS, PermissionAction::REJECT],
        ];
    }

    #[DataProvider('task_manager_extra_permissions')]
    public function test_task_manager_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('task_manager', $resource, $action);
    }

    public static function task_manager_extra_permissions(): array
    {
        return [
            [PermissionResource::TASKS, PermissionAction::COMPLETE],
            [PermissionResource::TASKS, PermissionAction::CANCEL],
            [PermissionResource::TASKS, PermissionAction::REJECT],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_WORKERS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_MATERIALS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_EQUIPMENT],
            [PermissionResource::MINI_TASKS, PermissionAction::COMPLETE],
        ];
    }

    #[DataProvider('mini_task_manager_extra_permissions')]
    public function test_mini_task_manager_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('mini_task_manager', $resource, $action);
    }

    public static function mini_task_manager_extra_permissions(): array
    {
        return [
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_WORKERS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_MATERIALS],
            [PermissionResource::MINI_TASKS, PermissionAction::ASSIGN_EQUIPMENT],
            [PermissionResource::MINI_TASKS, PermissionAction::COMPLETE],
        ];
    }

    #[DataProvider('work_log_manager_extra_permissions')]
    public function test_work_log_manager_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('work_log_manager', $resource, $action);
    }

    public static function work_log_manager_extra_permissions(): array
    {
        return [
            [PermissionResource::WORK_LOGS, PermissionAction::COMPLETE],
            [PermissionResource::WORK_LOGS, PermissionAction::APPROVE],
            [PermissionResource::WORK_LOGS, PermissionAction::REJECT],
        ];
    }

    #[DataProvider('ticket_manager_extra_permissions')]
    public function test_ticket_manager_extra_permissions(PermissionResource $resource, PermissionAction $action): void
    {
        $this->roleHasPermission('ticket_manager', $resource, $action);
    }

    public static function ticket_manager_extra_permissions(): array
    {
        return [
            [PermissionResource::TICKETS, PermissionAction::CONVERT],
            [PermissionResource::TICKETS, PermissionAction::REJECT],
        ];
    }
}
