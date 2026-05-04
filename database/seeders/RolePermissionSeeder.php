<?php

namespace Database\Seeders;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');

        $roleResourceActions = [
            'admin' => [
                'resources' => PermissionResource::cases(),
                'actions'   => PermissionAction::cases(),
            ],
            'manager' => [
                'resources' => [
                    PermissionResource::USERS,
                    PermissionResource::CLIENTS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::SERVICE_TYPES,
                    PermissionResource::TASKS,
                    PermissionResource::MINI_TASKS,
                    PermissionResource::WORK_LOGS,
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::EQUIPMENT_REVISIONS,
                    PermissionResource::SECTORS,
                    PermissionResource::TEAMS,
                    PermissionResource::WORKERS,
                    PermissionResource::MATERIALS,
                    PermissionResource::UNITS,
                    PermissionResource::ATTACHMENTS,
                    PermissionResource::PROFILE,
                    PermissionResource::SESSIONS,
                    PermissionResource::LOGIN_HISTORIES,
                ],
                // manager can view all operational, create+update on most
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'equipment_manager' => [
                'resources' => [
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::EQUIPMENT_REVISIONS,
                    PermissionResource::ATTACHMENTS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::SECTORS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'supervisor' => [
                'resources' => [
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::TASKS,
                    PermissionResource::MINI_TASKS,
                    PermissionResource::WORK_LOGS,
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::SECTORS,
                    PermissionResource::TEAMS,
                    PermissionResource::WORKERS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
            ],
            'worker' => [
                'resources' => [
                    PermissionResource::TASKS,
                    PermissionResource::MINI_TASKS,
                    PermissionResource::WORK_LOGS,
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
            ],
            'client' => [
                'resources' => [
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW],
            ],
        ];

        foreach ($roleResourceActions as $roleName => $config) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            foreach ($config['resources'] as $resource) {
                foreach ($config['actions'] as $action) {
                    $exists = DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('resource', $resource->value)
                        ->where('action', $action->value)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permissions')->insert([
                            'id'          => Str::uuid(),
                            'role_id'     => $roleId,
                            'resource'    => $resource->value,
                            'action'      => $action->value,
                            'description' => null,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]);
                    }
                }
            }
        }
    }
}
