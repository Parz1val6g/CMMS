<?php
namespace App\Features\Tasks\Listeners;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\TaskStatus;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\Notifications\Services\NotificationService;
class CheckMiniTasksCompletion
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }
    public function handle(MiniTaskCompletedEvent $event): void
    {
        $task = $event->miniTask->task;
        $hasIncompleteMiniTasks = $task->miniTasks()
            ->where('status', '!=', MiniTaskStatus::COMPLETED->value)
            ->exists();
        if (!$hasIncompleteMiniTasks) {
            $task->update(['status' => TaskStatus::AWAITING_APPROVAL->value]);
            $this->notificationService->create(
                $task->manager_id,
                __('messages.services.notifications.task_awaiting_approval_title', ['reference' => $task->reference]),
                __('messages.services.notifications.task_awaiting_approval_body'),
                'task_awaiting_approval'
            );
        }
    }
}