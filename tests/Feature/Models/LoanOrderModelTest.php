<?php

namespace Tests\Feature\Models;

use App\Core\Enums\LoanOrderStatus;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\Equipments\Models\Equipment;
use App\Features\Tasks\Models\Task;
use Tests\TestCase;

class LoanOrderModelTest extends TestCase
{
    public function test_can_create_loan_order_with_factory(): void
    {
        $loanOrder = LoanOrder::factory()->create([
            'manager_id' => $this->manager->id,
        ]);

        $this->assertNotNull($loanOrder->id);
        $this->assertNotNull($loanOrder->reference);
        $this->assertStringStartsWith('EMP', $loanOrder->reference);
        $this->assertEquals(LoanOrderStatus::PENDING, $loanOrder->status);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $loanOrder = LoanOrder::factory()->create([
            'status' => 'checked_out',
            'manager_id' => $this->manager->id,
        ]);

        $this->assertInstanceOf(LoanOrderStatus::class, $loanOrder->status);
        $this->assertTrue($loanOrder->status === LoanOrderStatus::CHECKED_OUT);
    }

    public function test_equipments_relationship(): void
    {
        $loanOrder = LoanOrder::factory()
            ->hasAttached(Equipment::factory()->count(2), [], 'equipments')
            ->create(['manager_id' => $this->manager->id]);

        $this->assertCount(2, $loanOrder->equipments);
        $this->assertInstanceOf(Equipment::class, $loanOrder->equipments->first());
    }

    public function test_tasks_relationship_morph_many(): void
    {
        $loanOrder = LoanOrder::factory()
            ->has(Task::factory()->count(2)->withManager($this->manager), 'tasks')
            ->create(['manager_id' => $this->manager->id]);

        $this->assertCount(2, $loanOrder->tasks);
        $this->assertInstanceOf(Task::class, $loanOrder->tasks->first());

        // Verify the morph data
        $task = $loanOrder->tasks->first();
        $this->assertEquals($loanOrder->id, $task->taskable_id);
        $this->assertEquals(LoanOrder::class, $task->taskable_type);
    }

    public function test_task_taskable_returns_loan_order(): void
    {
        $loanOrder = LoanOrder::factory()
            ->has(Task::factory()->withManager($this->manager), 'tasks')
            ->create(['manager_id' => $this->manager->id]);

        $task = $loanOrder->tasks->first();
        $this->assertInstanceOf(LoanOrder::class, $task->taskable);
        $this->assertEquals($loanOrder->id, $task->taskable->id);
    }
}
