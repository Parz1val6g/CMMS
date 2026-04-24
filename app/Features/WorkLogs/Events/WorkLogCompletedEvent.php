<?php
namespace App\Features\WorkLogs\Events;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class WorkLogCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(
        public WorkLog $workLog
    ) {}
}