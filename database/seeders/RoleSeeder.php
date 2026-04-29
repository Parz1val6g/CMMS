<?php

namespace Database\Seeders;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => Str::uuid(), 'name' => 'admin'],
            ['id' => Str::uuid(), 'name' => 'manager'],
            ['id' => Str::uuid(), 'name' => 'supervisor'],
            ['id' => Str::uuid(), 'name' => 'worker'],
            ['id' => Str::uuid(), 'name' => 'client'],
        ];

        foreach ($roles as &$role) {
            DB::table('roles')->insert($role);
        }

        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $managerRole = DB::table('roles')->where('name', 'manager')->first();
        $resources = PermissionResource::cases();
        $actions = PermissionAction::cases();

        // Admin gets ALL permissions on ALL resources
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $adminRole->id)
                    ->where('resource', $resource->value)
                    ->where('action', $action->value)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'id' => Str::uuid(),
                        'role_id' => $adminRole->id,
                        'resource' => $resource->value,
                        'action' => $action->value,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Manager gets view + create on core operational resources
        $managerResources = [
            PermissionResource::SERVICE_ORDERS,
            PermissionResource::TASKS,
            PermissionResource::MINI_TASKS,
            PermissionResource::WORK_LOGS,
            PermissionResource::CLIENTS,
            PermissionResource::LOCATIONS,
        ];
        $managerActions = [PermissionAction::VIEW, PermissionAction::CREATE];
        foreach ($managerResources as $resource) {
            foreach ($managerActions as $action) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $managerRole->id)
                    ->where('resource', $resource->value)
                    ->where('action', $action->value)
                    ->exists();

                if (!$exists) {
                    DB::table('role_permissions')->insert([
                        'id' => Str::uuid(),
                        'role_id' => $managerRole->id,
                        'resource' => $resource->value,
                        'action' => $action->value,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
