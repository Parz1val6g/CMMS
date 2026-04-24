<?php
namespace App\Features\Tasks\Listeners;
use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\Tasks\Services\TaskService;
class CheckMiniTasksCompletion
{
    public function __construct(
        private TaskService $taskService
    ) {
    }
    public function handle(MiniTaskCompletedEvent $event): void
    {
        $task = $event->miniTask->task;
        // Check if there are ANY mini-tasks for this task that are NOT completed
        $hasIncompleteMiniTasks = $task->miniTasks()
            ->where('status', '!=', MiniTaskStatus::COMPLETED->value)
            ->exists();
        // If none are incomplete, it means ALL are completed! Auto-complete the Task.
        if (!$hasIncompleteMiniTasks) {
            $this->taskService->complete($task);
        }
    }
}