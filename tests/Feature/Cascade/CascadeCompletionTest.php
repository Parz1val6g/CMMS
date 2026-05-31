<?php

namespace Tests\Feature\Cascade;

use Tests\TestCase;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\Locations\Models\Location;
use App\Features\Clients\Models\Client;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;
use App\Core\Enums\WorkLogStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Notifications\Models\Notification;

class CascadeCompletionTest extends TestCase
{
    private ServiceOrder $serviceOrder;
    private Task $task;
    private MiniTask $miniTask;
    private WorkLog $workLog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create required parent records explicitly (factories lack defaults)
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $location = Location::factory()->create([
            'parish_id' => $parish->id,
            'landmark' => 'Test landmark',
        ]);
        $client = Client::factory()->create([
            'user_id' => $this->manager->id,
        ]);
        $serviceType = ServiceType::factory()->create();

        // Create hierarchy: ServiceOrder → Task → MiniTask → WorkLog
        $this->serviceOrder = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'client_id' => $client->id,
            'location_id' => $location->id,
            'service_type_id' => $serviceType->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $this->task = Task::factory()->create([
            'service_order_id' => $this->serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-10',
        ]);

        $this->miniTask = MiniTask::factory()->create([
            'task_id' => $this->task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::PENDING->value,
        ]);

        $this->workLog = WorkLog::factory()->create([
            'mini_task_id' => $this->miniTask->id,
            'status' => WorkLogStatus::IN_PROGRESS->value,
        ]);
    }

    /**
     * Test: Complete WorkLog → MiniTask auto-completes → Task moves to AWAITING_APPROVAL
     * Triggers: WorkLogCompletedEvent → CheckWorkLogsCompletion → MiniTaskCompletedEvent → CheckMiniTasksCompletion
     */
    public function test_worklog_submitted_triggers_task_to_awaiting_approval(): void
    {
        $this->workLog->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        $this->workLog->update([
            'status' => WorkLogStatus::APPROVED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        $this->miniTask->refresh();
        $this->assertEquals(MiniTaskStatus::COMPLETED, $this->miniTask->status);

        $this->task->refresh();
        $this->assertEquals(TaskStatus::AWAITING_APPROVAL, $this->task->status);

        $this->serviceOrder->refresh();
        $this->assertNotEquals(ServiceOrderStatus::COMPLETED, $this->serviceOrder->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->task->manager_id,
            'type' => 'task_awaiting_approval',
        ]);
    }

    /**
     * Test: Incomplete WorkLog blocks cascade
     * If MiniTask has multiple WorkLogs, cascade only fires when ALL are completed
     */
    public function test_incomplete_worklog_blocks_cascade(): void
    {
        // Arrange: Create second worklog (still open, no completed_at)
        $workLog2 = WorkLog::factory()->create([
            'mini_task_id' => $this->miniTask->id,
            'completed_at' => null,
            'status' => WorkLogStatus::IN_PROGRESS->value,
        ]);

        // Act: Complete only first worklog
        $this->workLog->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        // Assert: MiniTask does NOT complete (second worklog incomplete)
        $this->miniTask->refresh();
        $this->assertEquals(MiniTaskStatus::PENDING, $this->miniTask->status);

        // Assert: Task does NOT complete
        $this->task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $this->task->status);
    }

    /**
     * Test: All WorkLogs completed → Task moves to AWAITING_APPROVAL
     */
    public function test_all_worklogs_completed_cascades_to_awaiting_approval(): void
    {
        $workLog2 = WorkLog::factory()->create([
            'mini_task_id' => $this->miniTask->id,
            'completed_at' => null,
            'status' => WorkLogStatus::IN_PROGRESS->value,
        ]);

        $this->workLog->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);
        $this->workLog->update([
            'status' => WorkLogStatus::APPROVED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        $workLog2->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($workLog2);
        $workLog2->update([
            'status' => WorkLogStatus::APPROVED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($workLog2);

        $this->miniTask->refresh();
        $this->task->refresh();
        $this->serviceOrder->refresh();

        $this->assertEquals(MiniTaskStatus::COMPLETED, $this->miniTask->status);
        $this->assertEquals(TaskStatus::AWAITING_APPROVAL, $this->task->status);
        $this->assertNotEquals(ServiceOrderStatus::COMPLETED, $this->serviceOrder->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->task->manager_id,
            'type' => 'task_awaiting_approval',
        ]);
    }

    /**
     * Test: Incomplete MiniTasks block Task completion
     */
    public function test_incomplete_minitasks_block_task_completion(): void
    {
        // Arrange: Create second MiniTask (still pending)
        $miniTask2 = MiniTask::factory()->create([
            'task_id' => $this->task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::PENDING->value,
        ]);

        // Act: Complete first miniTask
        $this->miniTask->update(['status' => MiniTaskStatus::COMPLETED->value]);
        \App\Features\MiniTasks\Events\MiniTaskCompletedEvent::dispatch($this->miniTask);

        // Assert: Task does NOT complete (second miniTask pending)
        $this->task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $this->task->status);
    }

    /**
     * Test: Incomplete Tasks block ServiceOrder completion
     */
    public function test_incomplete_tasks_block_serviceorder_completion(): void
    {
        // Arrange: Create second Task (still pending)
        $task2 = Task::factory()->create([
            'service_order_id' => $this->serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
        ]);

        // Act: Complete first task
        $this->task->update(['status' => TaskStatus::COMPLETED->value]);
        \App\Features\Tasks\Events\TaskCompletedEvent::dispatch($this->task);

        // Assert: ServiceOrder does NOT complete (second task pending)
        $this->serviceOrder->refresh();
        $this->assertEquals(ServiceOrderStatus::PENDING, $this->serviceOrder->status);
    }

    /**
     * Test: All Tasks COMPLETED → ServiceOrder moves to AWAITING_APPROVAL (not COMPLETED)
     * Triggers: TaskCompletedEvent → CheckTaskCompletion listener
     */
    public function test_all_tasks_completed_sets_serviceorder_to_awaiting_approval(): void
    {
        $this->task->update(['status' => TaskStatus::COMPLETED->value]);
        \App\Features\Tasks\Events\TaskCompletedEvent::dispatch($this->task);

        $this->serviceOrder->refresh();
        $this->assertEquals(ServiceOrderStatus::AWAITING_APPROVAL, $this->serviceOrder->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->serviceOrder->manager_id,
            'type' => 'service_order_awaiting_approval',
        ]);
    }
}
