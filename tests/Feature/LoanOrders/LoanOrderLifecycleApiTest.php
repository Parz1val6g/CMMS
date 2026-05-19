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

class LoanOrderLifecycleApiTest extends TestCase
{
    public function test_return_requires_checked_out_status(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        // Create loan → PENDING
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // Attempt return on PENDING → 422
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/return");
        $response->assertStatus(422);
    }

    public function test_cancel_pending_loan(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/cancel");

        $response->assertOk();
        $this->assertEquals(LoanOrderStatus::CANCELLED->value, $response->json('data.status'));
        $this->assertNotNull($response->json('data.cancelled_at'));

        // Equipment released back to ACTIVE
        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::ACTIVE, $equipment->status);
    }

    public function test_initiate_return_creates_return_task(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        // Create loan
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // Manually transition to CHECKED_OUT with completed checkout task
        $loanOrder = LoanOrder::findOrFail($id);
        $loanOrder->update(['status' => LoanOrderStatus::CHECKED_OUT->value]);

        $task = Task::where('taskable_id', $id)
            ->where('taskable_type', LoanOrder::class)
            ->first();
        $task->update(['status' => TaskStatus::COMPLETED->value]);

        // Initiate return
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/return");

        $response->assertSuccessful();
        $this->assertEquals(__('messages.task_names.equipment_return'), $response->json('data.description'));
        $this->assertEquals(TaskStatus::PENDING->value, $response->json('data.status'));
    }

    public function test_duplicate_return_returns_422(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // Set up CHECKED_OUT
        $loanOrder = LoanOrder::findOrFail($id);
        $loanOrder->update(['status' => LoanOrderStatus::CHECKED_OUT->value]);

        $task = Task::where('taskable_id', $id)
            ->where('taskable_type', LoanOrder::class)
            ->first();
        $task->update(['status' => TaskStatus::COMPLETED->value]);

        // First return — OK
        $this->actingAsUser()->postJson("/api/loan-orders/{$id}/return")->assertSuccessful();

        // Duplicate return — 422
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/return");
        $response->assertStatus(422);
    }

    public function test_cancel_checked_out_returns_422(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // Transition to CHECKED_OUT
        $loanOrder = LoanOrder::findOrFail($id);
        $loanOrder->update(['status' => LoanOrderStatus::CHECKED_OUT->value]);

        // Cancel CHECKED_OUT → 403 (policy denies non-PENDING cancel)
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/cancel");

        $response->assertStatus(403);
        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::IN_USE, $equipment->status);
    }

    public function test_double_cancel_is_idempotent(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // First cancel
        $this->actingAsUser()->postJson("/api/loan-orders/{$id}/cancel")->assertOk();

        // Second cancel — idempotent
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id}/cancel");
        $response->assertOk();
        $this->assertEquals(LoanOrderStatus::CANCELLED->value, $response->json('data.status'));
    }

    public function test_soft_delete_pending_loan(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        $response = $this->actingAsUser()->deleteJson("/api/loan-orders/{$id}");

        $response->assertOk();
        $this->assertSoftDeleted('loan_orders', ['id' => $id]);
    }

    public function test_delete_checked_out_returns_422(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');

        // Transition to CHECKED_OUT
        $loanOrder = LoanOrder::findOrFail($id);
        $loanOrder->update(['status' => LoanOrderStatus::CHECKED_OUT->value]);

        // Cannot delete non-terminal
        $response = $this->actingAsUser()->deleteJson("/api/loan-orders/{$id}");
        $response->assertStatus(422);
    }

    // ─── Full lifecycle ────────────────────────────────

    public function test_full_lifecycle(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $entityId = $this->createEntityId();

        // 1. Create → PENDING
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id = $response->json('data.id');
        $this->assertEquals(LoanOrderStatus::PENDING->value, $response->json('data.status'));

        // 2. Return on PENDING → 422
        $this->actingAsUser()->postJson("/api/loan-orders/{$id}/return")->assertStatus(422);

        // 3. Cancel PENDING → CANCELLED
        $this->actingAsUser()->postJson("/api/loan-orders/{$id}/cancel")->assertOk();

        // 4. Create another
        $response = $this->actingAsUser()->postJson('/api/loan-orders', [
            'entity_id'      => $entityId,
            'manager_id'     => $this->user->id,
            'equipment_ids'  => [$equipment->id],
        ]);
        $id2 = $response->json('data.id');

        // 5. Set CHECKED_OUT + complete checkout task
        $loanOrder = LoanOrder::findOrFail($id2);
        $loanOrder->update(['status' => LoanOrderStatus::CHECKED_OUT->value]);
        $loanOrder->refresh();
        Task::where('taskable_id', $id2)
            ->where('taskable_type', LoanOrder::class)
            ->update(['status' => TaskStatus::COMPLETED->value]);

        // 6. Initiate return → return task created
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id2}/return");
        $response->assertSuccessful();
        $this->assertEquals(__('messages.task_names.equipment_return'), $response->json('data.description'));

        // 7. Duplicate return → 422
        $this->actingAsUser()->postJson("/api/loan-orders/{$id2}/return")->assertStatus(422);

        // 8. Cancel CHECKED_OUT → 403 (policy denies non-PENDING cancel)
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id2}/cancel");
        $response->assertStatus(403);

        // Equipment still IN_USE
        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::IN_USE, $equipment->status);

        // 9. Complete the return → RETURNED
        $response = $this->actingAsUser()->postJson("/api/loan-orders/{$id2}/complete");
        $response->assertSuccessful();
        $this->assertEquals(LoanOrderStatus::RETURNED->value, $response->json('data.status'));

        // Equipment back to ACTIVE
        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::ACTIVE, $equipment->status);

        // 10. Soft delete RETURNED
        $this->actingAsUser()->deleteJson("/api/loan-orders/{$id2}")->assertOk();
        $this->assertSoftDeleted('loan_orders', ['id' => $id2]);
    }

    // ─── helpers ───────────────────────────────────────

    private function createEntityId(): string
    {
        return Entity::factory()->create()->id;
    }
}
