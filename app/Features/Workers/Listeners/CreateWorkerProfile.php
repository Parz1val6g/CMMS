<?php
namespace App\Features\Workers\Listeners;
use App\Features\Admin\Events\UserCreatedEvent;
use App\Features\Workers\Models\Worker;
class CreateWorkerProfile
{
    public function handle(UserCreatedEvent $event): void
    {
        $user = $event->user;
        // Check if the user has a Role named 'Worker'
        $isWorker = $user->roles()->where('name', 'Worker')->exists();
        if ($isWorker && !$user->workerProfile()->exists()) {
            Worker::create(['user_id' => $user->id]);
        }
    }
}