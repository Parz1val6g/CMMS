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
                    PermissionResource::EQUIPMENT_TYPES,
                    PermissionResource::COUNTING_TYPES,
                    PermissionResource::SECTORS,
                    PermissionResource::TEAMS,
                    PermissionResource::WORKERS,
                    PermissionResource::MATERIALS,
                    PermissionResource::UNITS,
                    PermissionResource::ATTACHMENTS,
                    PermissionResource::LOAN_ORDERS,
                    PermissionResource::ENTITIES,
                    PermissionResource::PROFILE,
                    PermissionResource::SESSIONS,
                    PermissionResource::LOGIN_HISTORIES,
                    PermissionResource::TICKETS,
                ],
                // manager can view all operational, create+update on most
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'equipment_manager' => [
                'resources' => [
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::EQUIPMENT_REVISIONS,
                    PermissionResource::EQUIPMENT_TYPES,
                    PermissionResource::COUNTING_TYPES,
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
            'task_manager' => [
                'resources' => [
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::TASKS,
                    PermissionResource::ATTACHMENTS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'mini_task_manager' => [
                'resources' => [
                    PermissionResource::TASKS,
                    PermissionResource::MINI_TASKS,
                    PermissionResource::ATTACHMENTS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'work_log_manager' => [
                'resources' => [
                    PermissionResource::MINI_TASKS,
                    PermissionResource::WORK_LOGS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'sector_manager' => [
                'resources' => [
                    PermissionResource::SECTORS,
                    PermissionResource::TEAMS,
                    PermissionResource::WORKERS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            ],
            'ticket_manager' => [
                'resources' => [
                    PermissionResource::TICKETS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE, PermissionAction::DELETE],
            ],
            'entidade' => [
                'resources' => [
                    PermissionResource::LOAN_ORDERS,
                    PermissionResource::ENTITIES,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE],
            ],
            'team_manager' => [
                'resources' => [
                    PermissionResource::TEAMS,
                    PermissionResource::WORKERS,
                    PermissionResource::MINI_TASKS,
                    PermissionResource::WORK_LOGS,
                    PermissionResource::TASKS,
                    PermissionResource::EQUIPMENTS,
                    PermissionResource::LOCATIONS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
            ],
            'attendant' => [
                'resources' => [
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE],
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

        // Custom abilities — per-resource action grants not covered by the matrix above
        $customAbilities = [
            'manager' => [
                'service_orders' => ['activate', 'complete', 'cancel', 'delete'],
                'tasks'          => ['cancel', 'complete', 'reject'],
                'mini_tasks'     => ['assign_workers', 'assign_materials', 'assign_equipment', 'complete'],
                'loan_orders'    => ['approve', 'checkout', 'cancel', 'complete', 'initiate_return', 'delete'],
                'work_logs'      => ['complete', 'approve', 'reject'],
            ],
            'supervisor' => [
                'service_orders' => ['complete'],
                'tasks'          => ['complete', 'cancel', 'reject'],
            ],
            'task_manager' => [
                'tasks'      => ['complete', 'cancel', 'reject'],
                'mini_tasks' => ['assign_workers', 'assign_materials', 'assign_equipment', 'complete'],
            ],
            'mini_task_manager' => [
                'mini_tasks' => ['assign_workers', 'assign_materials', 'assign_equipment', 'complete'],
            ],
            'work_log_manager' => [
                'work_logs' => ['complete', 'approve', 'reject'],
            ],
            'ticket_manager' => [
                'tickets' => ['convert', 'reject'],
            ],
        ];

        foreach ($customAbilities as $roleName => $resourceActions) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            foreach ($resourceActions as $resource => $actions) {
                foreach ($actions as $action) {
                    $exists = DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->where('resource', $resource)
                        ->where('action', $action)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permissions')->insert([
                            'id'          => Str::uuid(),
                            'role_id'     => $roleId,
                            'resource'    => $resource,
                            'action'      => $action,
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
