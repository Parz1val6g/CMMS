<?php
namespace App\Features\Tasks\Events;
use App\Features\Tasks\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class TaskCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(
        public Task $task
    ) {
    }
}