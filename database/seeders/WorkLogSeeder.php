<?php

namespace Database\Seeders;

use App\Core\Enums\WorkLogStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WorkLogSeeder extends Seeder
{
    public function run(): void
    {
        $miniTasks = DB::table('mini_tasks')->where('status', 'completed')->get();
        $workers = DB::table('workers')->get();
        $reviewers = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager']);
                    });
            })->get();

        if ($miniTasks->isEmpty() || $workers->isEmpty()) {
            return;
        }

        $descriptions = [
            'Execução dos trabalhos conforme planeado',
            'Trabalho concluído dentro do tempo previsto',
            'Necessário material adicional para conclusão',
            'Trabalho executado com equipamento próprio',
            'Conclusão antecipada dos trabalhos',
            'Trabalho realizado com apoio de equipa extra',
        ];

        foreach ($miniTasks as $miniTask) {
            // 80% of completed mini-tasks have work logs
            if (rand(1, 100) > 80) {
                continue;
            }

            $startedAt = fake()->dateTimeBetween($miniTask->created_at, '+7 days');
            $durationHours = rand(1, 8);
            $completedAt = (clone $startedAt)->modify('+' . $durationHours . ' hours');

            // Get random workers assigned (1-3)
            $assignedWorkers = $workers->random(rand(1, min(3, $workers->count())));

            $workLogId = Str::uuid();
            $durationMinutes = $durationHours * 60;

            DB::table('work_logs')->insert([
                'id' => $workLogId,
                'mini_task_id' => $miniTask->id,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'description' => $descriptions[array_rand($descriptions)],
                'duration_minutes' => $durationMinutes,
                'status' => WorkLogStatus::APPROVED->value,
                'reviewed_by' => $reviewers->isNotEmpty() ? $reviewers->random()->id : null,
                'reviewed_at' => (clone $completedAt)->modify('+1 day'),
                'created_at' => $startedAt,
                'updated_at' => $completedAt,
            ]);

            // Assign workers to this work log (composite PK: work_log_id + worker_id)
            foreach ($assignedWorkers as $worker) {
                DB::table('work_logs_workers')->insert([
                    'work_log_id' => $workLogId,
                    'worker_id' => $worker->id,
                    'created_at' => $startedAt,
                    'updated_at' => $startedAt,
                ]);
            }

            // 40% chance of using materials
            if (rand(1, 100) <= 40) {
                $materials = DB::table('materials')->inRandomOrder()->limit(rand(1, 3))->get();
                foreach ($materials as $material) {
                    DB::table('work_logs_materials')->insert([
                        'id' => Str::uuid(),
                        'work_log_id' => $workLogId,
                        'material_id' => $material->id,
                        'quantity_used' => round(rand(1, 100) / 10, 1),
                        'unit_price_at_use' => round(rand(50, 5000) / 100, 2),
                        'created_at' => $startedAt,
                        'updated_at' => $startedAt,
                    ]);
                }
            }

            // Assign workers & teams to the mini-task (for the pivot table)
            foreach ($assignedWorkers as $worker) {
                $exists = DB::table('mini_tasks_workers_teams')
                    ->where('mini_task_id', $miniTask->id)
                    ->where('worker_id', $worker->id)
                    ->exists();

                if (!$exists) {
                    DB::table('mini_tasks_workers_teams')->insert([
                        'id' => Str::uuid(),
                        'mini_task_id' => $miniTask->id,
                        'worker_id' => $worker->id,
                        'team_id' => $worker->team_id,
                        'created_at' => $startedAt,
                        'updated_at' => $startedAt,
                    ]);
                }
            }
        }
    }
}
