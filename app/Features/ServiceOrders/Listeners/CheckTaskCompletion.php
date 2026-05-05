<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Features\Tasks\Events\TaskCompletedEvent;
use App\Features\ServiceOrders\Services\ServiceOrderService;

class CheckTaskCompletion
{
    public function __construct(
        private ServiceOrderService $serviceOrderService
    ) {}

    public function handle(TaskCompletedEvent $event): void
    {
        $serviceOrder = $event->task->serviceOrder;

        // Loan guard: don't close SO if Task 2 ("Devolução") doesn't exist yet
        if ($serviceOrder->workflow_type === WorkflowType::LOAN->value) {
            $hasTask2 = $serviceOrder->tasks()
                ->where('name', 'Devolução de Equipamento')
                ->exists();
            if (!$hasTask2) {
                return;
            }
        }

        $hasIncompleteTasks = $serviceOrder->tasks()
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->exists();

        if (!$hasIncompleteTasks) {
            $this->serviceOrderService->complete($serviceOrder);
        }
    }
}
