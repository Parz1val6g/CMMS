<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MiniTaskWorkerTeamSeeder extends Seeder
{
    public function run(): void
    {
        $miniTasks = DB::table('mini_tasks')->pluck('id');
        $workers   = DB::table('workers')->pluck('id');
        $teams     = DB::table('teams')->pluck('id');

        if ($miniTasks->isEmpty()) return;

        // Assign first mini-task to first worker (if exists)
        if ($workers->isNotEmpty()) {
            $exists = DB::table('mini_tasks_workers_teams')
                ->where('mini_task_id', $miniTasks[0])
                ->where('worker_id', $workers[0])
                ->exists();

            if (!$exists) {
                DB::table('mini_tasks_workers_teams')->insert([
                    'id'           => Str::uuid(),
                    'mini_task_id' => $miniTasks[0],
                    'worker_id'    => $workers[0],
                    'team_id'      => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Assign second mini-task to first team (if exists)
        if (isset($miniTasks[1]) && $teams->isNotEmpty()) {
            $exists = DB::table('mini_tasks_workers_teams')
                ->where('mini_task_id', $miniTasks[1])
                ->where('team_id', $teams[0])
                ->exists();

            if (!$exists) {
                DB::table('mini_tasks_workers_teams')->insert([
                    'id'           => Str::uuid(),
                    'mini_task_id' => $miniTasks[1],
                    'worker_id'    => null,
                    'team_id'      => $teams[0],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
