<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Tasks\Models\Task;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class TaskCompleteTest extends TestCase
{
    private function createTask(TaskStatus $status = TaskStatus::AWAITING_APPROVAL): Task
    {
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $location = Location::factory()->create(['parish_id' => $parish->id, 'landmark' => 'Test landmark']);
        $client = Client::factory()->create(['user_id' => $this->manager->id]);
        $serviceType = ServiceType::factory()->create();

        $serviceOrder = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'client_id' => $client->id,
            'location_id' => $location->id,
            'service_type_id' => $serviceType->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        return Task::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => $status->value,
        ]);
    }

    public function test_complete_requires_authentication(): void
    {
        $task = $this->createTask();

        $response = $this->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(401);
    }

    public function test_worker_cannot_complete_task(): void
    {
        $task = $this->createTask();

        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(403);
    }

    public function test_manager_can_complete_task_in_awaiting_approval(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
        $this->assertEquals(TaskStatus::COMPLETED->value, $response->json('data.status'));

        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
    }

    public function test_admin_can_complete_task(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
        $this->assertEquals(TaskStatus::COMPLETED->value, $response->json('data.status'));
    }

    public function test_cannot_complete_task_not_in_awaiting_approval(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(422);

        $task->refresh();
        $this->assertEquals(TaskStatus::PENDING, $task->status);
    }

    public function test_completing_task_dispatches_task_completed_event(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
    }
}
