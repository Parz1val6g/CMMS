<?php

namespace Tests\Feature\LoanOrders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;
use Tests\TestCase;

class LoanOrderApiTest extends TestCase
{
    public function test_create_loan_order_requires_auth(): void
    {
        $response = $this->postJson('/api/loan-orders', []);

        $response->assertUnauthorized();
    }

    public function test_create_loan_order_returns_201_with_valid_data(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
            'description'    => 'Test loan order',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'reference', 'status', 'entity', 'manager', 'equipments', 'tasks'],
            ]);

        $this->assertEquals(LoanOrderStatus::PENDING->value, $response->json('data.status'));
        $this->assertStringStartsWith('EMP', $response->json('data.reference'));

        // Equipment marked IN_USE
        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::IN_USE, $equipment->status);

        // Checkout Task auto-created
        $this->assertCount(1, $response->json('data.tasks'));
        $this->assertEquals(TaskStatus::PENDING->value, $response->json('data.tasks.0.status'));
    }

    public function test_create_loan_order_with_location(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();
        $parish = \App\Shared\Models\Parish::first();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
            'parish_id'      => $parish->id,
            'street'         => 'Rua Principal',
            'postal_code'    => '3500-000',
            'reference_point'=> 'Ao lado do mercado',
        ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.location'));
        $this->assertEquals($parish->id, $response->json('data.location.parish_id'));
        $this->assertEquals('Rua Principal', $response->json('data.location.street'));
    }

    public function test_create_loan_order_validation_fails(): void
    {
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'     => 'invalid-uuid',
            'equipment_ids' => 'not-an-array',
        ]);

        $response->assertStatus(422);
    }

    public function test_get_loan_order_returns_detail(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $createResponse = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);

        $id = $createResponse->json('data.id');

        $response = $this->actingAsUser()->getJson("/api/loan-orders/{$id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'reference', 'status', 'entity', 'manager', 'equipments', 'tasks'],
            ]);

        $this->assertEquals($id, $response->json('data.id'));
        $this->assertCount(1, $response->json('data.equipments'));
        $this->assertCount(1, $response->json('data.tasks'));
    }

    public function test_get_loan_order_not_found(): void
    {
        $response = $this->actingAsUser()->getJson('/api/loan-orders/00000000-0000-0000-0000-000000000000');

        $response->assertNotFound();
    }

    public function test_list_loan_orders_returns_paginated(): void
    {
        $entityId = $this->createEntityId();

        LoanOrder::factory()->count(3)->create([
            'entity_id'  => $entityId,
            'manager_id' => $this->user->id,
        ]);

        $response = $this->actingAsUser()->getJson('/api/loan-orders');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ]);
    }

    public function test_create_throws_422_if_equipment_unavailable(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        // First creation consumes it
        $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);

        // Second attempt should fail — equipment is now IN_USE
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);

        $response->assertStatus(422);
    }

    // ─── helpers ───────────────────────────────────────

    private function createEntityId(): string
    {
        return Entity::factory()->create()->id;
    }
}
