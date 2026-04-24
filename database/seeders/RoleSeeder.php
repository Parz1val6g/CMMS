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
            ['id' => Str::uuid(), 'name' => 'pending'],
        ];

        foreach ($roles as &$role) {
            DB::table('roles')->insert($role);
        }

        // Seedar todas as permissões para admin
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $resources = PermissionResource::cases();
        $actions = PermissionAction::cases();

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
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
}
