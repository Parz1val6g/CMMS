<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Sectors\Models\Sector;
use App\Shared\Models\Parish;

class TaskProgressGateTest extends TestCase
{
    private Sector $sector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sector = Sector::factory()->create(['head_id' => $this->manager->id]);
    }

    private function createServiceOrder(): ServiceOrder
    {
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $location = Location::factory()->create(['parish_id' => $parish->id, 'landmark' => 'Test landmark']);
        $client = Client::factory()->create(['user_id' => $this->manager->id]);
        $serviceType = ServiceType::factory()->create();

        return ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'client_id' => $client->id,
            'location_id' => $location->id,
            'service_type_id' => $serviceType->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
    }

    private function createTask(TaskStatus $status = TaskStatus::PENDING, array $attrs = []): Task
    {
        $serviceOrder = $this->createServiceOrder();

        return Task::factory()->create(array_merge([
            'service_order_id' => $serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => $status->value,
        ], $attrs));
    }

    // ── Gate 1: Task cancel blocked without period ──

    public function test_cannot_cancel_task_without_execution_period(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/cancel");

        $response->assertStatus(422);

        $task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $task->status);
    }

    public function test_can_cancel_task_with_execution_period(): void
    {
        $task = $this->createTask(TaskStatus::PENDING, [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-10',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals(TaskStatus::CANCELLED->value, $response->json('data.status'));
    }

    // ── Gate 2: MiniTask creation blocked without parent period ──

    private function validMiniTaskData(Task $task): array
    {
        return [
            'task_id' => $task->id,
            'description' => 'New mini-task under this task',
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-08',
        ];
    }

    public function test_cannot_create_mini_task_without_parent_period(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/mini-tasks', $this->validMiniTaskData($task));

        $response->assertStatus(422);
    }

    public function test_can_create_mini_task_with_parent_period(): void
    {
        $task = $this->createTask(TaskStatus::PENDING, [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-10',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/mini-tasks', $this->validMiniTaskData($task));

        $response->assertStatus(201);
    }

    // ── Gate 1 (cascade): Task without period blocked in cascade ──

    public function test_task_without_period_does_not_cascade_to_awaiting_approval(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $miniTask = MiniTask::factory()->create([
            'task_id' => $task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::COMPLETED->value,
        ]);

        \App\Features\MiniTasks\Events\MiniTaskCompletedEvent::dispatch($miniTask);

        $task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $task->status,
            'Task without execution period should not cascade to AWAITING_APPROVAL');
    }
}
