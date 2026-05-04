<?php

namespace Database\Seeders;

use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\WorkLogStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\Workers\Models\Worker;
use App\Features\Materials\Models\Material;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WorkLogSeeder extends Seeder
{
    private const DESCRIPTIONS = [
        'Execução dos trabalhos conforme planeado e dentro do prazo estipulado',
        'Trabalho concluído com qualidade e dentro do tempo previsto',
        'Necessário material adicional para conclusão da tarefa',
        'Trabalho executado com equipamento próprio da equipa',
        'Conclusão antecipada dos trabalhos face ao cronograma',
        'Trabalho realizado com apoio de equipa extra devido à complexidade',
        'Intervenção concluída com sucesso após adaptação de procedimentos',
        'Execução parcial com necessidade de continuação em turno seguinte',
    ];

    public function run(): void
    {
        $miniTasks = MiniTask::all();
        $workers = Worker::all();
        $materials = Material::all();
        $reviewers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();

        if ($miniTasks->isEmpty() || $workers->isEmpty()) {
            return;
        }

        foreach ($miniTasks as $miniTask) {
            $mtStatus = $miniTask->status;
            $this->createWorkLogs($miniTask, $mtStatus, $workers, $materials, $reviewers);
        }
    }

    private function createWorkLogs(MiniTask $miniTask, ?MiniTaskStatus $mtStatus, $workers, $materials, $reviewers): void
    {
        // Completed: 90% have work logs (1-3 per MT)
        // In_progress: 60% have work logs (1 per MT)
        // Others: no work logs
        $config = match ($mtStatus) {
            MiniTaskStatus::COMPLETED => ['chance' => 90, 'maxLogs' => 3],
            MiniTaskStatus::IN_PROGRESS => ['chance' => 60, 'maxLogs' => 1],
            default => null,
        };

        if ($config === null || rand(1, 100) > $config['chance']) {
            return;
        }

        $numLogs = rand(1, $config['maxLogs']);

        for ($i = 0; $i < $numLogs; $i++) {
            $startedAt = fake()->dateTimeBetween($miniTask->created_at, '+14 days');
            // Avoid DST spring-forward gap (Europe/Lisbon: Mar last Sun 01:00-02:00)
            if ($startedAt->format('Y-m-d') === '2026-03-29' && $startedAt->format('H') === '01') {
                $startedAt->modify('+1 hour');
            }
            $durationHours = rand(1, 8);
            $completedAt = (clone $startedAt)->modify('+' . $durationHours . ' hours');

            $reviewedAt = null;
            if ($mtStatus === MiniTaskStatus::COMPLETED) {
                $reviewedAt = (clone $completedAt)->modify('+1 day');
                // Avoid DST spring-forward gap (Europe/Lisbon: Mar last Sun 01:00-02:00)
                if ($reviewedAt->format('Y-m-d') === '2026-03-29' && $reviewedAt->format('H') === '01') {
                    $reviewedAt->modify('+1 hour');
                }
            }

            $workLog = WorkLog::create([
                'mini_task_id' => $miniTask->id,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                'status' => $mtStatus === MiniTaskStatus::COMPLETED
                    ? WorkLogStatus::APPROVED->value
                    : WorkLogStatus::SUBMITTED->value,
                'reviewed_by' => $reviewers->isNotEmpty() && $mtStatus === MiniTaskStatus::COMPLETED
                    ? $reviewers->random()->id
                    : null,
                'reviewed_at' => $reviewedAt,
                'created_at' => $startedAt,
                'updated_at' => $completedAt,
            ]);

            // Attach 1-3 workers from the mini-task's assigned workers, or random workers
            $assignedWorkers = $miniTask->workers()->exists()
                ? $miniTask->workers()->inRandomOrder()->take(rand(1, min(3, $miniTask->workers()->count())))->get()
                : $workers->random(rand(1, min(3, $workers->count())));

            foreach ($assignedWorkers as $worker) {
                $workLog->workers()->attach($worker->id, [
                    'created_at' => $startedAt,
                    'updated_at' => $startedAt,
                ]);
            }

            // 40% chance of using materials (use DB due to UUID primary key on pivot)
            if ($materials->isNotEmpty() && rand(1, 100) <= 40) {
                $usedMaterials = $materials->random(rand(1, min(3, $materials->count())));

                foreach ($usedMaterials as $material) {
                    DB::table('work_logs_materials')->insert([
                        'id' => Str::uuid(),
                        'work_log_id' => $workLog->id,
                        'material_id' => $material->id,
                        'quantity_used' => round(rand(10, 500) / 10, 1),
                        'unit_price_at_use' => round(rand(50, 5000) / 100, 2),
                        'created_at' => $startedAt,
                        'updated_at' => $startedAt,
                    ]);
                }
            }
        }
    }
}
