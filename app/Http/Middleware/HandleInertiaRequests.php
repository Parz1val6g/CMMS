<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'first_name' => $request->user()->first_name,
                    'last_name' => $request->user()->last_name,
                    'email' => $request->user()->email,
                ] : null,
            ],
            'can' => $request->user() ? [
                'viewDashboard' => $request->user()->can('viewDashboard'),
                'viewUsers' => $request->user()->can('viewAny', \App\Shared\Models\User::class),
                'manageUsers' => $request->user()->can('create', \App\Shared\Models\User::class),
                'viewRoles' => $request->user()->can('viewAny', \App\Shared\Models\Role::class),
                'manageRoles' => $request->user()->can('create', \App\Shared\Models\Role::class),
                'viewServiceOrders' => $request->user()->can('viewAny', \App\Features\ServiceOrders\Models\ServiceOrder::class),
                'createServiceOrders' => $request->user()->can('create', \App\Features\ServiceOrders\Models\ServiceOrder::class),
                'viewTasks' => $request->user()->can('viewAny', \App\Features\Tasks\Models\Task::class),
                'createTasks' => $request->user()->can('create', \App\Features\Tasks\Models\Task::class),
                'viewMiniTasks' => $request->user()->can('viewAny', \App\Features\MiniTasks\Models\MiniTask::class),
                'viewWorkLogs' => $request->user()->can('viewAny', \App\Features\WorkLogs\Models\WorkLog::class),
                'viewClients' => $request->user()->can('viewAny', \App\Features\Clients\Models\Client::class),
                'createClients' => $request->user()->can('create', \App\Features\Clients\Models\Client::class),
                'viewEquipments' => $request->user()->can('viewAny', \App\Features\Equipments\Models\Equipment::class),
                'createEquipments' => $request->user()->can('create', \App\Features\Equipments\Models\Equipment::class),
                'viewMaterials' => $request->user()->can('viewAny', \App\Features\Materials\Models\Material::class),
                'createMaterials' => $request->user()->can('create', \App\Features\Materials\Models\Material::class),
                'viewLocations' => $request->user()->can('viewAny', \App\Features\Locations\Models\Location::class),
                'createLocations' => $request->user()->can('create', \App\Features\Locations\Models\Location::class),
                'viewSectors' => $request->user()->can('viewAny', \App\Features\Sectors\Models\Sector::class),
                'createSectors' => $request->user()->can('create', \App\Features\Sectors\Models\Sector::class),
                'viewTeams' => $request->user()->can('viewAny', \App\Features\Teams\Models\Team::class),
                'createTeams' => $request->user()->can('create', \App\Features\Teams\Models\Team::class),
                'viewWorkers' => $request->user()->can('viewAny', \App\Features\Workers\Models\Worker::class),
                'createWorkers' => $request->user()->can('create', \App\Features\Workers\Models\Worker::class),
                'viewServiceTypes' => $request->user()->can('viewAny', \App\Features\ServiceTypes\Models\ServiceType::class),
                'createServiceTypes' => $request->user()->can('create', \App\Features\ServiceTypes\Models\ServiceType::class),
                'viewNotifications' => $request->user()->can('viewAny', \App\Features\Notifications\Models\Notification::class),
                'viewSettings' => $request->user()->can('viewAny', \App\Shared\Models\AppSetting::class),
            ] : null,
            'flash' => [
                'success' => $request->session()->get('success')
                    ? e($request->session()->get('success')) : null,
                'error' => $request->session()->get('error')
                    ? e($request->session()->get('error')) : null,
            ],
            'googleMapsApiKey' => config('services.google_maps.api_key'),
        ];
    }
}
