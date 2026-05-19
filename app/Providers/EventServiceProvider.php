<?php
namespace App\Providers;

use App\Features\Admin\Events\UserCreatedEvent;
use App\Features\Clients\Listeners\CreateClientProfile;
use App\Features\MiniTasks\Events\MiniTaskCompletedEvent;
use App\Features\MiniTasks\Listeners\CheckWorkLogsCompletion;
use App\Features\Notifications\Listeners\SendServiceOrderCreatedNotification;
use App\Features\ServiceOrders\Events\ServiceOrderCreatedEvent;
use App\Features\ServiceOrders\Listeners\CheckTaskCompletion;
use App\Features\Tasks\Events\TaskCompletedEvent;
use App\Features\Tasks\Listeners\CheckMiniTasksCompletion;
use App\Features\WorkLogs\Events\WorkLogCompletedEvent;
use App\Features\Workers\Listeners\CreateWorkerProfile;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ServiceOrderCreatedEvent::class => [
            SendServiceOrderCreatedNotification::class,
        ],
        UserCreatedEvent::class => [
            CreateClientProfile::class,
            CreateWorkerProfile::class,
        ],
        WorkLogCompletedEvent::class => [
            CheckWorkLogsCompletion::class,
        ],
        MiniTaskCompletedEvent::class => [
            CheckMiniTasksCompletion::class,
        ],
        TaskCompletedEvent::class => [
            CheckTaskCompletion::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
