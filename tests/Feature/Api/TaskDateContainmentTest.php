<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class TaskDateContainmentTest extends TestCase
{
    private function createServiceOrder(array $dates): ServiceOrder
    {
        $parish = Parish::inRandomOrder()->first() ?? Parish::factory()->create();
        $location = Location::factory()->create(['parish_id' => $parish->id, 'landmark' => 'Test landmark']);
        $client = Client::factory()->create(['user_id' => $this->manager->id]);
        $serviceType = ServiceType::factory()->create();

        return ServiceOrder::factory()->create(array_merge([
            'manager_id' => $this->manager->id,
            'client_id' => $client->id,
            'location_id' => $location->id,
            'service_type_id' => $serviceType->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ], $dates));
    }

    // ── Slice 1: POST task start_date before SO start_date → 422 ──

    public function test_create_task_with_start_date_before_service_order_returns_422(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', [
                'service_order_id' => $so->id,
                'sector_id' => $sector->id,
                'description' => 'Test task',
                'start_date' => '2026-05-15',
                'end_date' => '2026-06-15',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
    }

    // ── Slice 2: POST task end_date after SO end_date → 422 ──

    public function test_create_task_with_end_date_after_service_order_returns_422(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', [
                'service_order_id' => $so->id,
                'sector_id' => $sector->id,
                'description' => 'Test task',
                'start_date' => '2026-06-15',
                'end_date' => '2026-07-15',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    // ── Slice 3: PATCH task dates outside SO → 422 ──

    public function test_update_task_dates_outside_service_order_returns_422(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $task = Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-10',
        ]);
        $task->sectors()->attach($sector->id);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-05-20',
                'end_date' => '2026-07-15',
            ]);

        $response->assertStatus(422);
    }

    // ── Slice 4: PATCH SO shrink dates with tasks outside → 422 ──

    public function test_shrink_service_order_dates_leaving_tasks_outside_returns_422(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-25',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/service-orders/{$so->id}", [
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-20',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
    }

    // ── Slice 5: PATCH SO shrink with no task conflict → 200 ──

    public function test_shrink_service_order_dates_with_no_conflict_succeeds(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);

        Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/service-orders/{$so->id}", [
                'start_date' => '2026-06-05',
                'end_date' => '2026-06-20',
            ]);

        $response->assertStatus(200);
    }

    // ── Slice 6: Tasks without period don't block SO date changes ──

    public function test_tasks_without_period_dont_block_service_order_date_changes(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);

        Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => null,
            'end_date' => null,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/service-orders/{$so->id}", [
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-20',
            ]);

        $response->assertStatus(200);
    }

    // ── Slice 7: PATCH task shrink past MiniTask → 422 ──

    public function test_shrink_task_dates_leaving_mini_tasks_outside_returns_422(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $task = Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-25',
        ]);
        $task->sectors()->attach($sector->id);

        MiniTask::factory()->create([
            'task_id' => $task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::PENDING->value,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-20',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-18',
            ]);

        $response->assertStatus(422);
    }

    // ── Slice 8: PATCH task shrink no conflict → 200 ──

    public function test_shrink_task_dates_with_no_mini_task_conflict_succeeds(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $task = Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-25',
        ]);
        $task->sectors()->attach($sector->id);

        MiniTask::factory()->create([
            'task_id' => $task->id,
            'supervisor_id' => $this->manager->id,
            'status' => MiniTaskStatus::PENDING->value,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-15',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-20',
            ]);

        $response->assertStatus(200);
    }

    // ── Slice 9: Tasks without mini-tasks period shrink is allowed ──

    public function test_shrink_task_without_mini_tasks_succeeds(): void
    {
        $so = $this->createServiceOrder(['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $task = Task::factory()->create([
            'service_order_id' => $so->id,
            'manager_id' => $this->manager->id,
            'status' => TaskStatus::PENDING->value,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-25',
        ]);
        $task->sectors()->attach($sector->id);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-06-15',
                'end_date' => '2026-06-18',
            ]);

        $response->assertStatus(200);
    }
}
