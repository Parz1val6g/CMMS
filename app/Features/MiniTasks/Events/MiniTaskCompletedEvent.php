<?php
namespace App\Features\MiniTasks\Events;
use App\Features\MiniTasks\Models\MiniTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MiniTaskCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(
        public MiniTask $miniTask
    ) {
    }
}