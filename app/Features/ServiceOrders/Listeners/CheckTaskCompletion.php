<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\ServiceOrderStatus;
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

        $hasIncompleteTasks = $serviceOrder->tasks()
            ->where('status', '!=', ServiceOrderStatus::COMPLETED->value)
            ->exists();

        if (!$hasIncompleteTasks) {
            $this->serviceOrderService->complete($serviceOrder);
        }
    }
}
