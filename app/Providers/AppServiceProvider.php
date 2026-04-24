<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\App\Features\ServiceOrders\Models\ServiceOrder::class, \App\Features\ServiceOrders\Policies\ServiceOrderPolicy::class);
        Gate::policy(\App\Features\Tasks\Models\Task::class, \App\Features\Tasks\Policies\TaskPolicy::class);
        Gate::policy(\App\Features\MiniTasks\Models\MiniTask::class, \App\Features\MiniTasks\Policies\MiniTaskPolicy::class);
        Gate::policy(\App\Features\WorkLogs\Models\WorkLog::class, \App\Features\WorkLogs\Policies\WorkLogPolicy::class);
    }
}
