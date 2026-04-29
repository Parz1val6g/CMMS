<?php

namespace Tests\Feature\Authorization;

use Tests\TestCase;
use App\Features\ServiceOrders\Models\ServiceOrder;

class ServiceOrderPoliciesTest extends TestCase
{
    private ServiceOrder $serviceOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // ServiceOrder owned by manager
        $this->serviceOrder = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
        ]);
    }

    public function test_admin_can_view_any_service_order(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(200, $response->status());
    }

    public function test_manager_can_view_own_service_order(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(200, $response->status());
    }

    public function test_manager_cannot_view_others_service_order(): void
    {
        $otherManager = $this->createUser('manager');

        $response = $this->actingAs($otherManager, 'sanctum')
            ->getJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(403, $response->status());
    }

    public function test_worker_cannot_view_service_order(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->getJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(403, $response->status());
    }

    public function test_unauthenticated_cannot_view_service_order(): void
    {
        $response = $this->getJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(401, $response->status());
    }

    public function test_manager_cannot_update_completed_service_order(): void
    {
        $completed = ServiceOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/service-orders/{$completed->id}", [
                'process' => 'Updated process',
            ]);

        $this->assertEquals(422, $response->status());
    }

    public function test_manager_cannot_delete_others_service_order(): void
    {
        $otherManager = $this->createUser('manager');

        $response = $this->actingAs($otherManager, 'sanctum')
            ->deleteJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(403, $response->status());
    }

    public function test_admin_can_delete_any_service_order(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/service-orders/{$this->serviceOrder->id}");

        $this->assertEquals(200, $response->status());
    }
}
