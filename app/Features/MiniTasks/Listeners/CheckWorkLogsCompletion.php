<?php

namespace App\Features\MiniTasks\Listeners;

use App\Core\Enums\WorkLogStatus;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\MiniTasks\Services\MiniTaskService;

class CheckWorkLogsCompletion
{
    public function __construct(
        private MiniTaskService $miniTaskService
    ) {}

    public function handle(WorkLogCompletedEvent $event): void
    {
        $miniTask = $event->workLog->miniTask;

        $hasIncompleteWorkLogs = $miniTask->workLogs()
            ->whereNull('completed_at')
            ->exists();

        if (!$hasIncompleteWorkLogs) {
            $this->miniTaskService->complete($miniTask);
        }
    }
}
