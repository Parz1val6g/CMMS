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

        /**
         * Each role maps to an array of groups.
         * A group is ['resources' => [...], 'actions' => [...]].
         * This allows different action sets per resource within the same role.
         *
         * UC1 handoff principle: each level has exclusive write authority over its resource,
         * and read-only access to resources above and below it.
         */
        $rolePermissions = [

            // ── Admin: unrestricted ──
            'admin' => [
                ['resources' => PermissionResource::cases(), 'actions' => PermissionAction::cases()],
            ],

            // ── Manager (Gestor SO): owns SOs, reads task state ──
            'manager' => [
                [
                    'resources' => [
                        PermissionResource::SERVICE_ORDERS,
                        PermissionResource::ATTACHMENTS,
                    ],
                    'actions' => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                        PermissionAction::DELETE,
                        PermissionAction::ACTIVATE,
                        PermissionAction::COMPLETE,
                        PermissionAction::CANCEL,
                    ],
                ],
                [
                    // Read task state + state-transition actions; no write on task data
                    'resources' => [PermissionResource::TASKS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::CANCEL,
                        PermissionAction::COMPLETE,
                        PermissionAction::REJECT,
                    ],
                ],
                [
                    // Reference data needed to create/activate SOs
                    'resources' => [
                        PermissionResource::USERS,
                        PermissionResource::CLIENTS,
                        PermissionResource::LOCATIONS,
                        PermissionResource::SERVICE_TYPES,
                        PermissionResource::SECTORS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Attendant (Atendente): creates SOs, sees only their own ──
            'attendant' => [
                [
                    'resources' => [PermissionResource::SERVICE_ORDERS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                        PermissionAction::DELETE, // only Pending SOs; enforced by policy
                    ],
                ],
                [
                    'resources' => [PermissionResource::ATTACHMENTS],
                    'actions'   => [PermissionAction::VIEW, PermissionAction::CREATE],
                ],
                [
                    // Reference data needed to fill the SO form
                    'resources' => [
                        PermissionResource::CLIENTS,
                        PermissionResource::LOCATIONS,
                        PermissionResource::SERVICE_TYPES,
                        PermissionResource::USERS,
                        PermissionResource::SECTORS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Task Manager (Gestor de Tarefa): owns Tasks + Mini-Tasks ──
            'task_manager' => [
                [
                    'resources' => [PermissionResource::TASKS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::UPDATE,
                        PermissionAction::CANCEL,
                        PermissionAction::COMPLETE,
                        PermissionAction::REJECT,
                    ],
                ],
                [
                    'resources' => [PermissionResource::MINI_TASKS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                        PermissionAction::ASSIGN_WORKERS,
                        PermissionAction::ASSIGN_MATERIALS,
                        PermissionAction::ASSIGN_EQUIPMENT,
                        PermissionAction::COMPLETE,
                    ],
                ],
                [
                    'resources' => [PermissionResource::ATTACHMENTS],
                    'actions'   => [PermissionAction::VIEW, PermissionAction::CREATE],
                ],
                [
                    // Reference data needed to plan mini-tasks
                    'resources' => [
                        PermissionResource::SERVICE_ORDERS,
                        PermissionResource::SECTORS,
                        PermissionResource::WORKERS,
                        PermissionResource::TEAMS,
                        PermissionResource::MATERIALS,
                        PermissionResource::EQUIPMENTS,
                        PermissionResource::UNITS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Worker (Trabalhador): executes mini-tasks, logs work ──
            'worker' => [
                [
                    'resources' => [PermissionResource::WORK_LOGS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                    ],
                ],
                [
                    // Updates own assigned mini-tasks (mark complete); cannot create them
                    'resources' => [PermissionResource::MINI_TASKS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::UPDATE,
                        PermissionAction::COMPLETE,
                    ],
                ],
                [
                    // Read-only access to tasks where assigned
                    'resources' => [
                        PermissionResource::TASKS,
                        PermissionResource::MATERIALS,
                        PermissionResource::EQUIPMENTS,
                        PermissionResource::LOCATIONS,
                        PermissionResource::UNITS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Sector Manager (Gestor de Setor): manages teams and workers ──
            'sector_manager' => [
                [
                    'resources' => [
                        PermissionResource::SECTORS,
                        PermissionResource::TEAMS,
                        PermissionResource::WORKERS,
                    ],
                    'actions' => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                    ],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Team Manager (Gestor de Equipa): manages team composition ──
            'team_manager' => [
                [
                    'resources' => [PermissionResource::TEAMS],
                    'actions'   => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
                [
                    'resources' => [PermissionResource::WORKERS],
                    'actions'   => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Client: no system access currently (UC1 §1) ──
            'client' => [
                [
                    'resources' => [PermissionResource::PROFILE],
                    'actions'   => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Equipment Manager (non-UC1 feature role) ──
            'equipment_manager' => [
                [
                    'resources' => [
                        PermissionResource::EQUIPMENTS,
                        PermissionResource::EQUIPMENT_REVISIONS,
                        PermissionResource::EQUIPMENT_TYPES,
                        PermissionResource::COUNTING_TYPES,
                        PermissionResource::ATTACHMENTS,
                    ],
                    'actions' => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                    ],
                ],
                [
                    'resources' => [
                        PermissionResource::LOCATIONS,
                        PermissionResource::SECTORS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Ticket Manager (non-UC1 feature role) ──
            'ticket_manager' => [
                [
                    'resources' => [PermissionResource::TICKETS],
                    'actions'   => [
                        PermissionAction::VIEW,
                        PermissionAction::CREATE,
                        PermissionAction::UPDATE,
                        PermissionAction::DELETE,
                        PermissionAction::CONVERT,
                        PermissionAction::REJECT,
                    ],
                ],
                [
                    // Reference data needed to process tickets
                    'resources' => [
                        PermissionResource::CLIENTS,
                        PermissionResource::LOCATIONS,
                        PermissionResource::SERVICE_TYPES,
                        PermissionResource::USERS,
                    ],
                    'actions' => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],

            // ── Entidade (non-UC1 feature role) ──
            'entidade' => [
                [
                    'resources' => [PermissionResource::LOAN_ORDERS],
                    'actions'   => [PermissionAction::VIEW, PermissionAction::CREATE],
                ],
                [
                    'resources' => [PermissionResource::ENTITIES],
                    'actions'   => [PermissionAction::VIEW],
                ],
                [
                    'resources' => [
                        PermissionResource::PROFILE,
                        PermissionResource::SESSIONS,
                        PermissionResource::LOGIN_HISTORIES,
                    ],
                    'actions' => [PermissionAction::VIEW, PermissionAction::UPDATE],
                ],
            ],
        ];

        foreach ($rolePermissions as $roleName => $groups) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            foreach ($groups as $group) {
                foreach ($group['resources'] as $resource) {
                    foreach ($group['actions'] as $action) {
                        $resourceValue = $resource instanceof PermissionResource
                            ? $resource->value
                            : $resource;
                        $actionValue = $action instanceof PermissionAction
                            ? $action->value
                            : $action;

                        $exists = DB::table('role_permissions')
                            ->where('role_id', $roleId)
                            ->where('resource', $resourceValue)
                            ->where('action', $actionValue)
                            ->exists();

                        if (!$exists) {
                            DB::table('role_permissions')->insert([
                                'id'          => Str::uuid(),
                                'role_id'     => $roleId,
                                'resource'    => $resourceValue,
                                'action'      => $actionValue,
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
}
