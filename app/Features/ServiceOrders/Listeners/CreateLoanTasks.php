<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Services\TransactionHandler;
use App\Features\ServiceOrders\Events\ServiceOrderCreatedEvent;
use App\Features\Tasks\Models\Task;

class CreateLoanTasks
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function handle(ServiceOrderCreatedEvent $event): void
    {
        $so = $event->serviceOrder;

        if ($so->workflow_type !== WorkflowType::LOAN) {
            return;
        }

        // Equipment status was already atomically set to in_use inside
        // ServiceOrderService::create() with lockForUpdate(). This listener
        // only creates the checkout task — no redundant equipment mutation.
        $this->transactions->execute(function () use ($so) {
            Task::create([
                'service_order_id' => $so->id,
                'manager_id'       => $so->manager_id,
                'description'      => __('messages.task_names.equipment_loan'),
                'status'           => TaskStatus::PENDING->value,
            ]);
        });
    }
}
