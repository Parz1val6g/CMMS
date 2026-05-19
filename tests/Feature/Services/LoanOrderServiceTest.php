<?php

namespace Tests\Feature\Services;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Exceptions\EquipmentUnavailableException;
use App\Core\Enums\EquipmentStatus;
use App\Features\Equipments\Models\Equipment;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\LoanOrders\Services\LoanOrderService;
use App\Features\Tasks\Models\Task;
use InvalidArgumentException;
use Tests\TestCase;

class LoanOrderServiceTest extends TestCase
{
    private LoanOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LoanOrderService::class);
    }

    public function test_create_creates_loan_order_with_pending_status(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $loanOrder = $this->service->create([
            'entity_id'      => $this->createEntityId(),
            'manager_id'     => $this->manager->id,
            'equipment_ids'  => [$equipment->id],
        ], $this->manager->id);

        $this->assertInstanceOf(LoanOrder::class, $loanOrder);
        $this->assertNotNull($loanOrder->id);
        $this->assertEquals(LoanOrderStatus::PENDING, $loanOrder->status);
        $this->assertStringStartsWith('EMP', $loanOrder->reference);
    }

    public function test_create_marks_equipment_as_in_use(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $this->service->create([
            'entity_id'      => $this->createEntityId(),
            'manager_id'     => $this->manager->id,
            'equipment_ids'  => [$equipment->id],
        ], $this->manager->id);

        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::IN_USE, $equipment->status);
    }

    public function test_create_attaches_equipment_to_loan_order(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $loanOrder = $this->service->create([
            'entity_id'      => $this->createEntityId(),
            'manager_id'     => $this->manager->id,
            'equipment_ids'  => [$equipment->id],
        ], $this->manager->id);

        $this->assertCount(1, $loanOrder->equipments);
        $this->assertEquals($equipment->id, $loanOrder->equipments->first()->id);
    }

    public function test_create_creates_checkout_task(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();

        $loanOrder = $this->service->create([
            'entity_id'      => $this->createEntityId(),
            'manager_id'     => $this->manager->id,
            'equipment_ids'  => [$equipment->id],
        ], $this->manager->id);

        $this->assertCount(1, $loanOrder->tasks);
        $task = $loanOrder->tasks->first();
        $this->assertEquals(TaskStatus::PENDING, $task->status);
        $this->assertEquals($loanOrder->id, $task->taskable_id);
        $this->assertEquals(LoanOrder::class, $task->taskable_type);
    }

    public function test_create_throws_if_equipment_unavailable(): void
    {
        $equipment = Equipment::factory()->loanable()->notLoanable()->create();

        $this->expectException(EquipmentUnavailableException::class);
        $this->service->create([
            'entity_id'      => $this->createEntityId(),
            'manager_id'     => $this->manager->id,
            'equipment_ids'  => [$equipment->id],
        ], $this->manager->id);
    }

    public function test_create_throws_if_no_equipment_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->create([
            'entity_id'  => $this->createEntityId(),
            'manager_id' => $this->manager->id,
        ], $this->manager->id);
    }

    public function test_complete_transitions_checked_out_to_returned(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $loanOrder = $this->createCheckedOutLoanOrder($equipment);

        $result = $this->service->complete($loanOrder);

        $this->assertEquals(LoanOrderStatus::RETURNED, $result->status);
        $this->assertNotNull($result->returned_at);
    }

    public function test_complete_releases_equipment(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $loanOrder = $this->createCheckedOutLoanOrder($equipment);

        $this->service->complete($loanOrder);

        $equipment->refresh();
        $this->assertEquals(EquipmentStatus::ACTIVE, $equipment->status);
    }

    public function test_complete_throws_if_not_checked_out(): void
    {
        $loanOrder = LoanOrder::factory()->create([
            'manager_id' => $this->manager->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->complete($loanOrder);
    }

    public function test_complete_throws_if_already_returned(): void
    {
        $loanOrder = LoanOrder::factory()->returned()->create([
            'manager_id' => $this->manager->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->complete($loanOrder);
    }

    public function test_cancel_transitions_pending_to_cancelled(): void
    {
        $loanOrder = LoanOrder::factory()->pending()->create([
            'manager_id' => $this->manager->id,
        ]);

        $result = $this->service->cancel($loanOrder, $this->manager->id);

        $this->assertEquals(LoanOrderStatus::CANCELLED, $result->status);
        $this->assertNotNull($result->cancelled_at);
    }

    public function test_cancel_throws_if_checked_out(): void
    {
        $equipment = Equipment::factory()->loanable()->active()->create();
        $loanOrder = $this->createCheckedOutLoanOrder($equipment);

        $this->expectException(InvalidArgumentException::class);
        $this->service->cancel($loanOrder, $this->manager->id);
    }

    public function test_cancel_throws_if_returned(): void
    {
        $loanOrder = LoanOrder::factory()->returned()->create([
            'manager_id' => $this->manager->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->cancel($loanOrder, $this->manager->id);
    }

    public function test_cancel_is_idempotent(): void
    {
        $loanOrder = LoanOrder::factory()->cancelled()->create([
            'manager_id' => $this->manager->id,
        ]);

        $result = $this->service->cancel($loanOrder, $this->manager->id);

        $this->assertEquals(LoanOrderStatus::CANCELLED, $result->status);
        $this->assertNotNull($result->cancelled_at);
    }

    // ─── helpers ───────────────────────────────────────

    private function createEntityId(): string
    {
        return \App\Features\Entities\Models\Entity::factory()->create()->id;
    }

    private function createCheckedOutLoanOrder(Equipment $equipment): LoanOrder
    {
        $loanOrder = LoanOrder::factory()->create([
            'manager_id' => $this->manager->id,
            'status'     => LoanOrderStatus::CHECKED_OUT->value,
        ]);
        $loanOrder->equipments()->attach($equipment->id);
        $equipment->markAsInUse();

        return $loanOrder;
    }
}
