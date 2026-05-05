<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * Each entry maps a frontend key to [ability, model|null].
     * null model → ability-only check (e.g. a Gate::define without a model).
     * Adding a new permission requires a single line here — nowhere else.
     */
    private const CAN_CHECKS = [
        'viewDashboard'      => ['viewDashboard', null],
        'viewUsers'          => ['viewAny',  \App\Shared\Models\User::class],
        'manageUsers'        => ['create',   \App\Shared\Models\User::class],
        'viewRoles'          => ['viewAny',  \App\Shared\Models\Role::class],
        'manageRoles'        => ['create',   \App\Shared\Models\Role::class],
        'viewServiceOrders'  => ['viewAny',  \App\Features\ServiceOrders\Models\ServiceOrder::class],
        'createServiceOrders'=> ['create',   \App\Features\ServiceOrders\Models\ServiceOrder::class],
        'viewTasks'          => ['viewAny',  \App\Features\Tasks\Models\Task::class],
        'createTasks'        => ['create',   \App\Features\Tasks\Models\Task::class],
        'viewMiniTasks'      => ['viewAny',  \App\Features\MiniTasks\Models\MiniTask::class],
        'viewWorkLogs'       => ['viewAny',  \App\Features\WorkLogs\Models\WorkLog::class],
        'viewClients'        => ['viewAny',  \App\Features\Clients\Models\Client::class],
        'createClients'      => ['create',   \App\Features\Clients\Models\Client::class],
        'viewEquipments'     => ['viewAny',  \App\Features\Equipments\Models\Equipment::class],
        'createEquipments'   => ['create',   \App\Features\Equipments\Models\Equipment::class],
        'viewMaterials'      => ['viewAny',  \App\Features\Materials\Models\Material::class],
        'createMaterials'    => ['create',   \App\Features\Materials\Models\Material::class],
        'viewLocations'      => ['viewAny',  \App\Features\Locations\Models\Location::class],
        'createLocations'    => ['create',   \App\Features\Locations\Models\Location::class],
        'viewSectors'        => ['viewAny',  \App\Features\Sectors\Models\Sector::class],
        'createSectors'      => ['create',   \App\Features\Sectors\Models\Sector::class],
        'viewTeams'          => ['viewAny',  \App\Features\Teams\Models\Team::class],
        'createTeams'        => ['create',   \App\Features\Teams\Models\Team::class],
        'viewWorkers'        => ['viewAny',  \App\Features\Workers\Models\Worker::class],
        'createWorkers'      => ['create',   \App\Features\Workers\Models\Worker::class],
        'viewServiceTypes'   => ['viewAny',  \App\Features\ServiceTypes\Models\ServiceType::class],
        'createServiceTypes' => ['create',   \App\Features\ServiceTypes\Models\ServiceType::class],
        'viewNotifications'  => ['viewAny',  \App\Features\Notifications\Models\Notification::class],
        'viewSettings'       => ['viewAny',  \App\Shared\Models\AppSetting::class],
    ];

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                ] : null,
            ],
            'can' => $user
                ? collect(self::CAN_CHECKS)
                    ->mapWithKeys(fn ($check, $key) => [
                        $key => $check[1] ? $user->can($check[0], $check[1]) : $user->can($check[0]),
                    ])
                    ->toArray()
                : null,
            'flash' => [
                'success' => $request->session()->get('success')
                    ? e($request->session()->get('success')) : null,
                'error'   => $request->session()->get('error')
                    ? e($request->session()->get('error')) : null,
            ],
        ];
    }
}
