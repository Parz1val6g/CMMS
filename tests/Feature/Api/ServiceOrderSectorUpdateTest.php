<?php

namespace Tests\Feature\Api;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use Tests\TestCase;

class ServiceOrderSectorUpdateTest extends TestCase
{
    public function test_add_sector_to_in_progress_creates_task(): void
    {
        $sector1 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $sector2 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector1->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/service-orders/{$order->id}/activate");

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'sector_ids' => [$sector1->id, $sector2->id],
            ]);

        $this->assertEquals(200, $response->status());

        $sector2Task = Task::where('service_order_id', $order->id)
            ->whereHas('sectors', fn($q) => $q->where('sectors.id', $sector2->id))
            ->first();

        $this->assertNotNull($sector2Task, 'Expected a Task to be created for the new sector.');
        $this->assertEquals(TaskStatus::PENDING, $sector2Task->status);
    }

    public function test_remove_sector_with_tasks_returns_422(): void
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

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'sector_ids' => [$sector2->id],
            ]);

        $this->assertEquals(422, $response->status());

        $order->refresh();
        $remainingIds = $order->sectors()->pluck('id')->all();
        $this->assertContains($sector1->id, $remainingIds, 'Sector with Task should not have been removed.');
    }

    public function test_remove_sector_without_tasks_is_allowed(): void
    {
        $sector1 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $sector2 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector1->id, $sector2->id]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'sector_ids' => [$sector1->id],
            ]);

        $this->assertEquals(200, $response->status());

        $order->refresh();
        $remainingIds = $order->sectors()->pluck('id')->all();
        $this->assertContains($sector1->id, $remainingIds);
        $this->assertNotContains($sector2->id, $remainingIds);
    }

    public function test_add_sector_to_pending_does_not_create_task(): void
    {
        $sector1 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $sector2 = Sector::factory()->create(['head_id' => $this->manager->id]);
        $order = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => ServiceOrderStatus::PENDING->value,
        ]);
        $order->sectors()->sync([$sector1->id]);

        $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$order->id}", [
                'sector_ids' => [$sector1->id, $sector2->id],
            ]);

        $taskForSector2 = Task::where('service_order_id', $order->id)
            ->whereHas('sectors', fn($q) => $q->where('sectors.id', $sector2->id))
            ->exists();

        $this->assertFalse($taskForSector2, 'No Task should be created for sector added to PENDING OS.');
    }
}
