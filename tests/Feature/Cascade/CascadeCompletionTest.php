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
        $client = Client::factory()->create();
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
     * Test: Complete WorkLog → MiniTask auto-completes (if all WorkLogs done)
     * Triggers: WorkLogCompletedEvent → CheckWorkLogsCompletion listener
     */
    public function test_worklog_submitted_triggers_cascade_to_completed(): void
    {
        // Act: Mark worklog as submitted (completed_at set, status = submitted)
        $this->workLog->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);

        // Fire event (triggers CheckWorkLogsCompletion → MiniTaskService::complete)
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        // Assert: MiniTask auto-completes
        $this->miniTask->refresh();
        $this->assertEquals(MiniTaskStatus::COMPLETED->value, $this->miniTask->status);

        // Assert: Task auto-completes
        $this->task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED->value, $this->task->status);

        // Assert: ServiceOrder auto-completes
        $this->serviceOrder->refresh();
        $this->assertEquals(ServiceOrderStatus::COMPLETED->value, $this->serviceOrder->status);
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
        $this->assertEquals(MiniTaskStatus::PENDING->value, $this->miniTask->status);

        // Assert: Task does NOT complete
        $this->task->refresh();
        $this->assertEquals(TaskStatus::PENDING->value, $this->task->status);
    }

    /**
     * Test: All WorkLogs completed → Full cascade propagates
     */
    public function test_all_worklogs_completed_cascades_fully(): void
    {
        // Arrange: Create second worklog (no completed_at initially)
        $workLog2 = WorkLog::factory()->create([
            'mini_task_id' => $this->miniTask->id,
            'completed_at' => null,
            'status' => WorkLogStatus::IN_PROGRESS->value,
        ]);

        // Act: Complete both worklogs
        $this->workLog->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($this->workLog);

        $workLog2->update([
            'completed_at' => now(),
            'status' => WorkLogStatus::SUBMITTED->value,
        ]);
        \App\Features\WorkLogs\Events\WorkLogCompletedEvent::dispatch($workLog2);

        // Assert: Full cascade completes
        $this->miniTask->refresh();
        $this->task->refresh();
        $this->serviceOrder->refresh();

        $this->assertEquals(MiniTaskStatus::COMPLETED->value, $this->miniTask->status);
        $this->assertEquals(TaskStatus::COMPLETED->value, $this->task->status);
        $this->assertEquals(ServiceOrderStatus::COMPLETED->value, $this->serviceOrder->status);
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
        $this->assertEquals(TaskStatus::PENDING->value, $this->task->status);
    }

    /**
     * Test: Multiple Tasks with one incomplete block ServiceOrder completion
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
        $this->assertEquals(ServiceOrderStatus::PENDING->value, $this->serviceOrder->status);
    }
}
