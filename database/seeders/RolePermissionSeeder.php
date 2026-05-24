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
            'attendant' => [
                'resources' => [
                    PermissionResource::SERVICE_ORDERS,
                    PermissionResource::PROFILE,
                ],
                'actions' => [PermissionAction::VIEW, PermissionAction::CREATE],
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
        ];

        // Per-resource overrides — grant extra actions beyond the role's default set
        $extraPermissions = [
            ['role' => 'manager', 'resource' => PermissionResource::SERVICE_ORDERS, 'actions' => [
                PermissionAction::DELETE,
                PermissionAction::CANCEL,
                PermissionAction::ACTIVATE,
                PermissionAction::COMPLETE,
            ]],
            ['role' => 'manager', 'resource' => PermissionResource::TASKS, 'actions' => [
                PermissionAction::CANCEL,
            ]],
            ['role' => 'manager', 'resource' => PermissionResource::MINI_TASKS, 'actions' => [
                PermissionAction::ASSIGN_WORKERS,
                PermissionAction::ASSIGN_MATERIALS,
                PermissionAction::ASSIGN_EQUIPMENT,
            ]],
            ['role' => 'manager', 'resource' => PermissionResource::LOAN_ORDERS, 'actions' => [
                PermissionAction::APPROVE,
                PermissionAction::CHECKOUT,
                PermissionAction::CANCEL,
                PermissionAction::COMPLETE,
                PermissionAction::INITIATE_RETURN,
            ]],
            ['role' => 'manager', 'resource' => PermissionResource::WORK_LOGS, 'actions' => [
                PermissionAction::COMPLETE,
                PermissionAction::APPROVE,
                PermissionAction::REJECT,
            ]],
            ['role' => 'supervisor', 'resource' => PermissionResource::SERVICE_ORDERS, 'actions' => [
                PermissionAction::COMPLETE,
            ]],
            ['role' => 'supervisor', 'resource' => PermissionResource::TASKS, 'actions' => [
                PermissionAction::COMPLETE,
                PermissionAction::CANCEL,
                PermissionAction::REJECT,
            ]],
            ['role' => 'task_manager', 'resource' => PermissionResource::TASKS, 'actions' => [
                PermissionAction::COMPLETE,
                PermissionAction::CANCEL,
                PermissionAction::REJECT,
            ]],
            ['role' => 'task_manager', 'resource' => PermissionResource::MINI_TASKS, 'actions' => [
                PermissionAction::ASSIGN_WORKERS,
                PermissionAction::ASSIGN_MATERIALS,
                PermissionAction::ASSIGN_EQUIPMENT,
                PermissionAction::COMPLETE,
            ]],
            ['role' => 'mini_task_manager', 'resource' => PermissionResource::MINI_TASKS, 'actions' => [
                PermissionAction::ASSIGN_WORKERS,
                PermissionAction::ASSIGN_MATERIALS,
                PermissionAction::ASSIGN_EQUIPMENT,
                PermissionAction::COMPLETE,
            ]],
            ['role' => 'work_log_manager', 'resource' => PermissionResource::WORK_LOGS, 'actions' => [
                PermissionAction::COMPLETE,
                PermissionAction::APPROVE,
                PermissionAction::REJECT,
            ]],
            ['role' => 'ticket_manager', 'resource' => PermissionResource::TICKETS, 'actions' => [
                PermissionAction::CONVERT,
                PermissionAction::REJECT,
            ]],
        ];

        foreach ($extraPermissions as $extra) {
            $roleId = $roles[$extra['role']] ?? null;
            if (!$roleId) continue;
            foreach ($extra['actions'] as $action) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('resource', $extra['resource']->value)
                    ->where('action', $action->value)
                    ->exists();
                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'id'          => Str::uuid(),
                        'role_id'     => $roleId,
                        'resource'    => $extra['resource']->value,
                        'action'      => $action->value,
                        'description' => null,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }

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
