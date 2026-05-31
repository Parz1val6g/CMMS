<?php
namespace App\Features\Tasks\Listeners;
use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\Notifications\Services\NotificationService;
use App\Features\Tasks\Services\TaskService;
class CheckMiniTasksCompletion
{
    // See docs/architecture/cascade-completion-chain.md for the full cascade documentation.
    public function __construct(
        private NotificationService $notificationService,
        private TaskService $taskService
    ) {
    }
    public function handle(MiniTaskCompletedEvent $event): void
    {
        $task = $event->miniTask->task;

        if (!$task->start_date || !$task->end_date) {
            return;
        }

        $hasIncompleteMiniTasks = $task->miniTasks()
            ->where('status', '!=', MiniTaskStatus::COMPLETED->value)
            ->exists();
        if (!$hasIncompleteMiniTasks) {
            $this->taskService->markAwaitingApproval($task);
            $this->notificationService->create(
                $task->manager_id,
                __('messages.services.notifications.task_awaiting_approval_title', ['reference' => $task->reference]),
                __('messages.services.notifications.task_awaiting_approval_body'),
                'task_awaiting_approval'
            );
        }
    }
}