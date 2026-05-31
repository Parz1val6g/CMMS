<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\Tasks\Models\Task;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\Parish;

class TaskPeriodTest extends TestCase
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
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
    }

    private function createTask(TaskStatus $status = TaskStatus::PENDING): Task
    {
        $serviceOrder = $this->createServiceOrder();

        return Task::factory()->create([
            'service_order_id' => $serviceOrder->id,
            'manager_id' => $this->manager->id,
            'status' => $status->value,
        ]);
    }

    private function validStoreData(array $overrides = []): array
    {
        return array_merge([
            'service_order_id' => $this->createServiceOrder()->id,
            'sector_id' => $this->sector->id,
            'description' => 'Task with execution period',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-10',
        ], $overrides);
    }

    // ── Slice 1: Create with start_date/end_date ──

    public function test_create_task_with_period_returns_201_with_dates(): void
    {
        $data = $this->validStoreData();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('data.start_date', '2026-06-01');
        $response->assertJsonPath('data.end_date', '2026-06-10');
    }

    // ── Slice 2: Required field validation ──

    public function test_start_date_is_required_on_create(): void
    {
        $data = $this->validStoreData(['start_date' => null]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date');
    }

    public function test_end_date_is_required_on_create(): void
    {
        $data = $this->validStoreData(['end_date' => null]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    // ── Slice 3: Date ordering validation ──

    public function test_end_date_before_start_date_returns_422(): void
    {
        $data = $this->validStoreData([
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-01',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('end_date');
    }

    // ── Slice 4: Update period in allowed statuses ──

    public function test_update_period_in_pending_succeeds(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-07-01',
                'end_date' => '2026-07-15',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.start_date', '2026-07-01');
        $response->assertJsonPath('data.end_date', '2026-07-15');
    }

    public function test_update_period_in_in_progress_succeeds(): void
    {
        $task = $this->createTask(TaskStatus::IN_PROGRESS);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-20',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.start_date', '2026-08-01');
        $response->assertJsonPath('data.end_date', '2026-08-20');
    }

    // ── Slice 5: Update period blocked for locked statuses ──

    public function test_update_period_in_awaiting_approval_returns_422(): void
    {
        $task = $this->createTask(TaskStatus::AWAITING_APPROVAL);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-10',
            ]);

        $response->assertStatus(422);
    }

    public function test_update_period_in_completed_returns_422(): void
    {
        $task = $this->createTask(TaskStatus::COMPLETED);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-10',
            ]);

        $response->assertStatus(422);
    }

    // ── Slice 6: TaskResource exposes dates ──

    public function test_task_resource_exposes_start_date_and_end_date(): void
    {
        $task = $this->createTask(TaskStatus::PENDING);
        $task->update(['start_date' => '2026-10-01', 'end_date' => '2026-10-15']);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.start_date', '2026-10-01');
        $response->assertJsonPath('data.end_date', '2026-10-15');
    }
}
