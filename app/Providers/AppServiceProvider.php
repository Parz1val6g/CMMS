<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(\App\Features\ServiceOrders\Models\ServiceOrder::class, \App\Features\ServiceOrders\Policies\ServiceOrderPolicy::class);
        Gate::policy(\App\Features\Tasks\Models\Task::class, \App\Features\Tasks\Policies\TaskPolicy::class);
        Gate::policy(\App\Features\MiniTasks\Models\MiniTask::class, \App\Features\MiniTasks\Policies\MiniTaskPolicy::class);
        Gate::policy(\App\Features\WorkLogs\Models\WorkLog::class, \App\Features\WorkLogs\Policies\WorkLogPolicy::class);
        Gate::policy(\App\Features\Clients\Models\Client::class, \App\Features\Clients\Policies\ClientPolicy::class);
        Gate::policy(\App\Features\Materials\Models\Material::class, \App\Features\Materials\Policies\MaterialPolicy::class);
        Gate::policy(\App\Features\Locations\Models\Location::class, \App\Features\Locations\Policies\LocationPolicy::class);
        Gate::policy(\App\Features\Notifications\Models\Notification::class, \App\Features\Notifications\Policies\NotificationPolicy::class);
        Gate::policy(\App\Shared\Models\AppSetting::class, \App\Features\Settings\Policies\AppSettingPolicy::class);
        Gate::policy(\App\Features\Sectors\Models\Sector::class, \App\Features\Sectors\Policies\SectorPolicy::class);
        Gate::policy(\App\Features\ServiceTypes\Models\ServiceType::class, \App\Features\ServiceTypes\Policies\ServiceTypePolicy::class);
        Gate::policy(\App\Features\Teams\Models\Team::class, \App\Features\Teams\Policies\TeamPolicy::class);
        Gate::policy(\App\Features\Workers\Models\Worker::class, \App\Features\Workers\Policies\WorkerPolicy::class);
        Gate::policy(\App\Shared\Models\User::class, \App\Shared\Policies\UserPolicy::class);
        Gate::policy(\App\Shared\Models\Attachment::class, \App\Shared\Policies\AttachmentPolicy::class);
        Gate::policy(\App\Shared\Models\Unit::class, \App\Shared\Policies\UnitPolicy::class);
        Gate::policy(\App\Shared\Models\Role::class, \App\Features\Admin\Policies\RolePolicy::class);
        Gate::policy(\App\Shared\Models\UserPreference::class, \App\Shared\Policies\UserPreferencePolicy::class);
    }
}
