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
            // ── UC1 roles ──
            'admin',
            'manager',
            'attendant',
            'task_manager',
            'worker',
            'client',
            'sector_manager',
            'team_manager',
            // ── Non-UC1 feature roles ──
            'equipment_manager',
            'ticket_manager',
            'entidade',
            'supervisor',
            'mini_task_manager',
            'work_log_manager',
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
