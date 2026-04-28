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

        $resourceActions = [
            'admin'   => PermissionAction::cases(),
            'manager' => [PermissionAction::VIEW, PermissionAction::CREATE, PermissionAction::UPDATE],
            'worker'  => [PermissionAction::VIEW, PermissionAction::UPDATE],
            'client'  => [PermissionAction::VIEW],
        ];

        $resources = PermissionResource::cases();

        foreach ($resourceActions as $roleName => $actions) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            foreach ($resources as $resource) {
                foreach ($actions as $action) {
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
