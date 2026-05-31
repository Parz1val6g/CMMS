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
use App\Shared\Models\Parish;

class MiniTaskDateContainmentTest extends TestCase
{
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
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
    }

    private function createTask(): Task
    {
        $serviceOrder = $this->createServiceOrder();

        return Task::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);
    }

    // ── Slice 1: POST mini-task start_date before task.start_date → 422 ──

    public function test_create_mini_task_with_start_date_before_task_start_date_returns_422(): void
    {
        $task = $this->createTask();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/mini-tasks', [
                'task_id' => $task->id,
                'description' => 'Mini-task outside period',
                'start_date' => '2026-05-01',
                'end_date' => '2026-06-15',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
    }

    // ── Slice 2: POST mini-task end_date after task.end_date → 422 ──

    public function test_create_mini_task_with_end_date_after_task_end_date_returns_422(): void
    {
        $task = $this->createTask();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/mini-tasks', [
                'task_id' => $task->id,
                'description' => 'Mini-task outside period',
                'start_date' => '2026-06-15',
                'end_date' => '2026-07-15',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    // ── Slice 3: PATCH mini-task dates outside task period → 422 ──

    public function test_update_mini_task_dates_outside_task_period_returns_422(): void
    {
        $task = $this->createTask();

        $miniTask = MiniTask::factory()->create([
            'task_id' => $task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-10',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/mini-tasks/{$miniTask->id}", [
                'start_date' => '2026-05-01',
                'end_date' => '2026-07-15',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
        $response->assertJsonValidationErrors('end_date');
    }
}
