<?php

namespace App\Http\Middleware;

use App\Core\Services\PermissionManager;
use App\Shared\Models\AppSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * Flat map: frontend key => 'resource:action' string.
     * Adding a new permission requires a single line here.
     */
    private const CAN_CHECKS = [
        'viewDashboard'          => 'viewDashboard',
        'viewUsers'              => 'users:view',
        'manageUsers'            => 'users:create',
        'viewRoles'              => 'roles:view',
        'manageRoles'            => 'roles:create',
        'viewServiceOrders'      => 'service_orders:view',
        'createServiceOrders'    => 'service_orders:create',
        'activateServiceOrder'   => 'service_orders:activate',
        'completeServiceOrder'   => 'service_orders:complete',
        'viewTasks'              => 'tasks:view',
        'createTasks'            => 'tasks:create',
        'completeTask'           => 'tasks:complete',
        'viewMiniTasks'          => 'mini_tasks:view',
        'assignWorkers'          => 'mini_tasks:assign_workers',
        'assignMaterials'        => 'mini_tasks:assign_materials',
        'assignEquipment'        => 'mini_tasks:assign_equipment',
        'viewWorkLogs'           => 'work_logs:view',
        'viewClients'            => 'clients:view',
        'createClients'          => 'clients:create',
        'viewEntities'           => 'entities:view',
        'viewEquipments'         => 'equipments:view',
        'createEquipments'       => 'equipments:create',
        'viewEquipmentTypes'     => 'equipment_types:view',
        'viewCountingTypes'      => 'counting_types:view',
        'viewMaterials'          => 'materials:view',
        'createMaterials'        => 'materials:create',
        'viewLocations'          => 'locations:view',
        'createLocations'        => 'locations:create',
        'viewSectors'            => 'sectors:view',
        'createSectors'          => 'sectors:create',
        'viewTeams'              => 'teams:view',
        'createTeams'            => 'teams:create',
        'viewWorkers'            => 'workers:view',
        'createWorkers'          => 'workers:create',
        'viewServiceTypes'       => 'service_types:view',
        'createServiceTypes'     => 'service_types:create',
        'createServiceTypes'     => 'service_types:create',
        'viewLoanOrders'         => 'loan_orders:view',
        'viewTickets'            => 'tickets:view',
        'viewNotifications'      => 'notifications:view',
        'viewSettings'           => 'settings:view',
    ];

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $can = null;
        $availableRoles = [];
        $activeRole = null;

        if ($user) {
            $permissionManager = app(PermissionManager::class);
            $activeRole = $request->session()->get('active_role');

            if ($activeRole) {
                $permissions = $permissionManager->activeRolePermissions($user, $activeRole);
            } else {
                $permissions = $permissionManager->userPermissions($user);
            }

            $can = collect(self::CAN_CHECKS)
                ->mapWithKeys(function ($permission, $key) use ($user, $permissions) {
                    if ($key === 'viewDashboard') {
                        return [$key => $user->can('viewDashboard')];
                    }
                    return [$key => in_array($permission, $permissions, true)];
                })
                ->toArray();

            $availableRoles = $user->roles()->get()->map(fn($role) => [
                'name'  => $role->name,
                'label' => __('enums.role_name.' . $role->name),
            ]);
        }

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
            'can'             => $can,
            'availableRoles'  => $availableRoles,
            'activeRole'      => $activeRole,
            'flash' => [
                'success' => $request->session()->get('success')
                    ? e($request->session()->get('success')) : null,
                'error'   => $request->session()->get('error')
                    ? e($request->session()->get('error')) : null,
            ],
            'googleMapsApiKey' => $user ? config('services.google_maps.api_key') : null,
            'companyLocation'  => (function () {
                $s = AppSetting::whereIn('key', ['company_district_id', 'company_municipality_id'])
                    ->pluck('value', 'key');
                $d = $s->get('company_district_id');
                $m = $s->get('company_municipality_id');
                return ($d || $m) ? ['district_id' => $d ?: null, 'municipality_id' => $m ?: null] : null;
            })(),
        ];
    }
}
