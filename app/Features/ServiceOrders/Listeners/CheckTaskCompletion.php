<?php

namespace App\Features\ServiceOrders\Listeners;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Services\TransactionHandler;
use App\Features\Notifications\Services\NotificationService;
use App\Features\Tasks\Events\TaskCompletedEvent;

class CheckTaskCompletion
{
    public function __construct(
        private TransactionHandler $transactions,
        private NotificationService $notificationService
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
            $this->transactions->execute(function () use ($serviceOrder) {
                $serviceOrder->update(['status' => ServiceOrderStatus::AWAITING_APPROVAL->value]);
            });

            $this->notificationService->create(
                $serviceOrder->manager_id,
                __('notifications.service_order.awaiting_approval.title'),
                __('notifications.service_order.awaiting_approval.message', ['process' => $serviceOrder->process]),
                'service_order_awaiting_approval'
            );
        }
    }
}
