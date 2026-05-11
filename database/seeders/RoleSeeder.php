<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'manager',
            'equipment_manager',
            'supervisor',
            'worker',
            'client',
            'task_manager',
            'mini_task_manager',
            'work_log_manager',
            'sector_manager',
        ];

        $existing = DB::table('roles')->whereIn('name', $roles)->pluck('name');

        foreach ($roles as $name) {
            if ($existing->contains($name)) continue;

            DB::table('roles')->insert([
                'id'   => Str::uuid(),
                'name' => $name,
            ]);
        }
    }
}
