<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \App\Exceptions\Handler::class
        );
    }

    public function boot(): void
    {
        // ── Audit Trail: Observe critical models ──
        \App\Features\ServiceOrders\Models\ServiceOrder::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Shared\Models\User::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Features\Tasks\Models\Task::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Features\MiniTasks\Models\MiniTask::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Features\WorkLogs\Models\WorkLog::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Shared\Models\Role::observe(\App\Shared\Observers\AuditObserver::class);
        \App\Features\Equipments\Models\Equipment::observe(\App\Shared\Observers\AuditObserver::class);

        // Override factory resolution for feature/shared namespaced models
        // Models: App\Shared\Models\{Name}, App\Features\{Feature}\Models\{Name}
        // Factories: Database\Factories\{Name}Factory (flat namespace, $model property set per factory)
        \Illuminate\Database\Eloquent\Factories\Factory::guessFactoryNamesUsing(function (string $modelClass): string {
            return 'Database\\Factories\\' . class_basename($modelClass) . 'Factory';
        });

        // Register model policies
        Gate::policy(\App\Features\Equipments\Models\Equipment::class, \App\Features\Equipments\Policies\EquipmentPolicy::class);
        Gate::policy(\App\Features\Equipments\Models\EquipmentRevision::class, \App\Features\Equipments\Policies\EquipmentRevisionPolicy::class);
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
        Gate::policy(\App\Features\Tickets\Models\Ticket::class, \App\Features\Tickets\Policies\TicketPolicy::class);
        Gate::policy(\App\Features\Entities\Models\Entity::class, \App\Features\Entities\Policies\EntityPolicy::class);
        Gate::policy(\App\Features\LoanOrders\Models\LoanOrder::class, \App\Features\LoanOrders\Policies\LoanOrderPolicy::class);

        // Dashboard access: any active authenticated user can view
        Gate::define('viewDashboard', function ($user) {
            return $user->status === 'active';
        });
    }
}
