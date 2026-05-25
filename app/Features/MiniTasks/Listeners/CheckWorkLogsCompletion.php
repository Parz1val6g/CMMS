<?php

namespace App\Features\MiniTasks\Listeners;

use App\Core\Enums\WorkLogStatus;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\MiniTasks\Services\MiniTaskService;

class CheckWorkLogsCompletion
{
    // See docs/architecture/cascade-completion-chain.md for the full cascade documentation.
    public function __construct(
        private MiniTaskService $miniTaskService
    ) {}

    public function handle(WorkLogCompletedEvent $event): void
    {
        $miniTask = $event->workLog->miniTask;

        // Per spec: a MiniTask is only complete when ALL WorkLogs are approved, not just submitted
        $hasIncompleteWorkLogs = $miniTask->workLogs()
            ->where('status', '!=', WorkLogStatus::APPROVED->value)
            ->exists();

        if (!$hasIncompleteWorkLogs) {
            $this->miniTaskService->complete($miniTask);
        }
    }
}
