<?php

namespace Tests\Feature\Authorization;

use App\Core\Services\PermissionManager;
use Tests\TestCase;

class RbacFoundationTest extends TestCase
{
    private PermissionManager $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissions = app(PermissionManager::class);
    }

    // ── Attendant ─────────────────────────────────────────────────────────

    public function test_attendant_has_service_orders_create_permission(): void
    {
        $attendant = $this->createUser('attendant');

        $this->assertTrue(
            $this->permissions->hasPermission($attendant, 'create', 'service_orders')
        );
    }

    public function test_attendant_has_service_orders_view_permission(): void
    {
        $attendant = $this->createUser('attendant');

        $this->assertTrue(
            $this->permissions->hasPermission($attendant, 'view', 'service_orders')
        );
    }

    public function test_attendant_cannot_activate_service_orders(): void
    {
        $attendant = $this->createUser('attendant');

        $this->assertFalse(
            $this->permissions->hasPermission($attendant, 'activate', 'service_orders')
        );
    }

    public function test_attendant_cannot_complete_service_orders(): void
    {
        $attendant = $this->createUser('attendant');

        $this->assertFalse(
            $this->permissions->hasPermission($attendant, 'complete', 'service_orders')
        );
    }

    // ── Manager ───────────────────────────────────────────────────────────

    public function test_manager_can_activate_service_orders(): void
    {
        $this->assertTrue(
            $this->permissions->hasPermission($this->manager, 'activate', 'service_orders')
        );
    }

    public function test_manager_can_complete_service_orders(): void
    {
        $this->assertTrue(
            $this->permissions->hasPermission($this->manager, 'complete', 'service_orders')
        );
    }

    public function test_manager_can_cancel_service_orders(): void
    {
        $this->assertTrue(
            $this->permissions->hasPermission($this->manager, 'cancel', 'service_orders')
        );
    }

    public function test_manager_can_approve_loan_orders(): void
    {
        $this->assertTrue(
            $this->permissions->hasPermission($this->manager, 'approve', 'loan_orders')
        );
    }

    public function test_manager_can_assign_workers_to_mini_tasks(): void
    {
        $this->assertTrue(
            $this->permissions->hasPermission($this->manager, 'assign_workers', 'mini_tasks')
        );
    }

    // ── Supervisor ────────────────────────────────────────────────────────

    public function test_supervisor_can_complete_service_orders(): void
    {
        $supervisor = $this->createUser('supervisor');

        $this->assertTrue(
            $this->permissions->hasPermission($supervisor, 'complete', 'service_orders')
        );
    }

    public function test_supervisor_cannot_activate_service_orders(): void
    {
        $supervisor = $this->createUser('supervisor');

        $this->assertFalse(
            $this->permissions->hasPermission($supervisor, 'activate', 'service_orders')
        );
    }

    public function test_supervisor_can_complete_tasks(): void
    {
        $supervisor = $this->createUser('supervisor');

        $this->assertTrue(
            $this->permissions->hasPermission($supervisor, 'complete', 'tasks')
        );
    }

    public function test_supervisor_can_cancel_tasks(): void
    {
        $supervisor = $this->createUser('supervisor');

        $this->assertTrue(
            $this->permissions->hasPermission($supervisor, 'cancel', 'tasks')
        );
    }

    public function test_supervisor_can_reject_tasks(): void
    {
        $supervisor = $this->createUser('supervisor');

        $this->assertTrue(
            $this->permissions->hasPermission($supervisor, 'reject', 'tasks')
        );
    }

    // ── Task Manager ──────────────────────────────────────────────────────

    public function test_task_manager_can_complete_tasks(): void
    {
        $taskManager = $this->createUser('task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($taskManager, 'complete', 'tasks')
        );
    }

    public function test_task_manager_can_assign_workers_to_mini_tasks(): void
    {
        $taskManager = $this->createUser('task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($taskManager, 'assign_workers', 'mini_tasks')
        );
    }

    public function test_task_manager_can_assign_materials_to_mini_tasks(): void
    {
        $taskManager = $this->createUser('task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($taskManager, 'assign_materials', 'mini_tasks')
        );
    }

    public function test_task_manager_can_assign_equipment_to_mini_tasks(): void
    {
        $taskManager = $this->createUser('task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($taskManager, 'assign_equipment', 'mini_tasks')
        );
    }

    public function test_task_manager_can_complete_mini_tasks(): void
    {
        $taskManager = $this->createUser('task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($taskManager, 'complete', 'mini_tasks')
        );
    }

    // ── Mini Task Manager ─────────────────────────────────────────────────

    public function test_mini_task_manager_can_assign_workers_to_mini_tasks(): void
    {
        $miniTaskManager = $this->createUser('mini_task_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($miniTaskManager, 'assign_workers', 'mini_tasks')
        );
    }

    public function test_mini_task_manager_cannot_complete_tasks(): void
    {
        $miniTaskManager = $this->createUser('mini_task_manager');

        $this->assertFalse(
            $this->permissions->hasPermission($miniTaskManager, 'complete', 'tasks')
        );
    }

    // ── Work Log Manager ──────────────────────────────────────────────────

    public function test_work_log_manager_can_complete_work_logs(): void
    {
        $workLogManager = $this->createUser('work_log_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($workLogManager, 'complete', 'work_logs')
        );
    }

    public function test_work_log_manager_can_approve_work_logs(): void
    {
        $workLogManager = $this->createUser('work_log_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($workLogManager, 'approve', 'work_logs')
        );
    }

    public function test_work_log_manager_can_reject_work_logs(): void
    {
        $workLogManager = $this->createUser('work_log_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($workLogManager, 'reject', 'work_logs')
        );
    }

    // ── Ticket Manager ────────────────────────────────────────────────────

    public function test_ticket_manager_can_convert_tickets(): void
    {
        $ticketManager = $this->createUser('ticket_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($ticketManager, 'convert', 'tickets')
        );
    }

    public function test_ticket_manager_can_reject_tickets(): void
    {
        $ticketManager = $this->createUser('ticket_manager');

        $this->assertTrue(
            $this->permissions->hasPermission($ticketManager, 'reject', 'tickets')
        );
    }

    // ── Admin bypass ──────────────────────────────────────────────────────

    public function test_admin_has_all_new_actions_on_service_orders(): void
    {
        foreach (['activate', 'complete', 'cancel'] as $action) {
            $this->assertTrue(
                $this->permissions->hasPermission($this->admin, $action, 'service_orders'),
                "Admin should have service_orders:{$action}"
            );
        }
    }
}
