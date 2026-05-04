<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\Equipment;
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

        if ($so->workflow_type !== WorkflowType::LOAN->value) {
            return;
        }

        $this->transactions->execute(function () use ($so) {
            Task::create([
                'service_order_id' => $so->id,
                'manager_id'       => $so->manager_id,
                'name'             => 'Empréstimo de Equipamento',
                'status'           => TaskStatus::PENDING->value,
            ]);

            if ($so->equipment_id) {
                Equipment::findOrFail($so->equipment_id)->markAsReserved();
            }
        });
    }
}
