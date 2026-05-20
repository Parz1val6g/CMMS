<?php

namespace Tests\Feature\Api;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use Tests\TestCase;

class ServiceOrderActivateTest extends TestCase
{
    public function test_activate_changes_status_to_in_progress(): void
    {
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(200, $response->status());
        $this->assertEquals('in_progress', $response->json('data.status'));
    }

    public function test_activate_creates_one_task_per_sector(): void
    {
        $sector1 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $sector2 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector1->id, $sector2->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(2, Task::where('service_order_id', $order->id)->count());

        foreach ([$sector1->id, $sector2->id] as $sectorId) {
            $this->assertTrue(
                Task::where('service_order_id', $order->id)
                    ->whereHas('sectors', fn($q) => $q->where('sectors.id', $sectorId))
                    ->exists()
            );
        }
    }

    public function test_activate_does_not_duplicate_existing_tasks(): void
    {
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector->id]);

        $existing = Task::factory()->create([
            'service_order_id' => $order->id,
            'manager_id'       => $this->manager->id,
            'status'           => TaskStatus::PENDING->value,
        ]);
        $existing->sectors()->sync([$sector->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(1, Task::where('service_order_id', $order->id)->count());
    }

    public function test_activate_fails_if_not_pending(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::IN_PROGRESS->value,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(422, $response->status());
    }

    public function test_admin_can_activate_any_service_order(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(200, $response->status());
    }

    public function test_other_manager_cannot_activate(): void
    {
        $otherManager = $this->createUser('manager');
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($otherManager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(403, $response->status());
    }

    public function test_worker_cannot_activate(): void
    {
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);

        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $this->assertEquals(403, $response->status());
    }

    public function test_create_does_not_create_tasks(): void
    {
        $sector = Sector::factory()->create(['head_id' => $this->manager->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/service-orders', [
                'manager_id'     => $this->manager->id,
                'sector_ids'     => [$sector->id],
                'execution_date' => '2026-06-15',
            ]);

        $this->assertEquals(0, Task::count());
    }
}
