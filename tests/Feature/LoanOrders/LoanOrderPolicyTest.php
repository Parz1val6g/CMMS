<?php

namespace Tests\Feature\LoanOrders;

use Tests\TestCase;
use App\Core\Enums\TaskStatus;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\Tasks\Models\Task;

class LoanOrderPolicyTest extends TestCase
{
    private LoanOrder $loanOrder;
    private LoanOrder $checkedOutLoan;

    protected function setUp(): void
    {
        parent::setUp();

        // LoanOrder owned by $this->manager
        $this->loanOrder = LoanOrder::factory()->create([
            'manager_id' => $this->manager->id,
        ]);

        // Checked-out LoanOrder with a completed task (required by service for return)
        $this->checkedOutLoan = LoanOrder::factory()->checkedOut()->create([
            'manager_id' => $this->manager->id,
        ]);
        Task::factory()->create([
            'taskable_id'   => $this->checkedOutLoan->id,
            'taskable_type' => LoanOrder::class,
            'manager_id'    => $this->manager->id,
            'status'        => TaskStatus::COMPLETED->value,
            'description'   => __('messages.task_names.equipment_loan'),
        ]);
    }

    // ── view (show) — manager-scoped ──

    public function test_admin_can_view_any_loan_order(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertOk();
    }

    public function test_manager_can_view_own_loan_order(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertOk();
    }

    public function test_manager_cannot_view_others_loan_order(): void
    {
        $otherManager = $this->createUser('manager');

        $response = $this->actingAs($otherManager, 'sanctum')
            ->getJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertForbidden();
    }

    public function test_worker_cannot_view_loan_order(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->getJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_view_loan_order(): void
    {
        $response = $this->getJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertUnauthorized();
    }

    // ── cancel — manager-scoped ──

    public function test_manager_can_cancel_own_loan_order(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/loan-orders/{$this->loanOrder->id}/cancel");

        $response->assertOk();
    }

    public function test_manager_cannot_cancel_others_loan_order(): void
    {
        $otherManager = $this->createUser('manager');

        $response = $this->actingAs($otherManager, 'sanctum')
            ->postJson("/api/loan-orders/{$this->loanOrder->id}/cancel");

        $response->assertForbidden();
    }

    public function test_admin_can_cancel_any_pending_loan_order(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/loan-orders/{$this->loanOrder->id}/cancel");

        $response->assertOk();
    }

    // ── delete — manager-scoped ──

    public function test_manager_cannot_delete_others_loan_order(): void
    {
        $otherManager = $this->createUser('manager');

        $response = $this->actingAs($otherManager, 'sanctum')
            ->deleteJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_loan_order(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/loan-orders/{$this->loanOrder->id}");

        $response->assertOk();
    }

    // ── initiateReturn — manager-scoped ──

    public function test_manager_can_initiate_return_on_own_checked_out_loan(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson("/api/loan-orders/{$this->checkedOutLoan->id}/return");

        $response->assertCreated();
    }

    public function test_worker_cannot_initiate_return(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->postJson("/api/loan-orders/{$this->checkedOutLoan->id}/return");

        $response->assertForbidden();
    }

    // ── list (viewAny) — permission-gated ──

    public function test_admin_can_list_loan_orders(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/loan-orders');

        $response->assertOk();
    }

    public function test_manager_can_list_loan_orders(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/loan-orders');

        $response->assertOk();
    }

    public function test_worker_cannot_list_loan_orders(): void
    {
        $response = $this->actingAs($this->worker, 'sanctum')
            ->getJson('/api/loan-orders');

        $response->assertForbidden();
    }
}
