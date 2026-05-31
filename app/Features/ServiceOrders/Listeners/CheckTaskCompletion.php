<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Features\Notifications\Services\NotificationService;
use App\Features\ServiceOrders\Services\ServiceOrderService;
use App\Features\Tasks\Events\TaskCompletedEvent;

class CheckTaskCompletion
{
    // See docs/architecture/cascade-completion-chain.md for the full cascade documentation.
    public function __construct(
        private NotificationService $notificationService,
        private ServiceOrderService $serviceOrderService
    ) {}

    public function handle(TaskCompletedEvent $event): void
    {
        $serviceOrder = $event->task->serviceOrder;

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
            $this->serviceOrderService->markAwaitingApproval($serviceOrder);

            $this->notificationService->create(
                $serviceOrder->manager_id,
                __('notifications.service_order.awaiting_approval.title'),
                __('notifications.service_order.awaiting_approval.message', ['process' => $serviceOrder->process]),
                'service_order_awaiting_approval'
            );
        }
    }
}
