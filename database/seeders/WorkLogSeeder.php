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
    /**
     * Create work logs covering every WorkLogStatus.
     * Relational consistency: COMPLETED mini-tasks → APPROVED work logs,
     * IN_PROGRESS → SUBMITTED, etc. Also includes REJECTED and IN_PROGRESS edge cases.
     */
    public function run(): void
    {
        $miniTasks = MiniTask::all();
        $workers = Worker::all();
        $materials = Material::all();
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $manager = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();

        if ($miniTasks->isEmpty() || $workers->isEmpty()) {
            return;
        }

        $reviewerPool = collect(array_filter([$admin, $manager]));

        $workLogDescriptions = [
            'Execução dos trabalhos conforme planeado e dentro do prazo estipulado',
            'Trabalho concluído com qualidade e dentro do tempo previsto',
            'Necessário material adicional para conclusão da tarefa',
            'Conclusão antecipada dos trabalhos face ao cronograma',
            'Intervenção concluída com sucesso após adaptação de procedimentos',
        ];

        /**
         * Map mini-task status → work log statuses to create
         * This guarantees every WorkLogStatus appears.
         */
        $statusMap = [
            MiniTaskStatus::COMPLETED->value   => [WorkLogStatus::APPROVED, WorkLogStatus::APPROVED],
            MiniTaskStatus::IN_PROGRESS->value => [WorkLogStatus::SUBMITTED],
            MiniTaskStatus::PENDING->value     => [], // no logs for pending
            MiniTaskStatus::BLOCKED->value     => [], // no logs for blocked
            MiniTaskStatus::CANCELLED->value   => [], // no logs for cancelled
        ];

        // Additional edge-case: create explicit IN_PROGRESS and REJECTED work logs
        $extraInProgress = true;
        $extraRejected = true;

        foreach ($miniTasks as $miniTask) {
            $wlStatuses = $statusMap[$miniTask->status->value] ?? [];

            foreach ($wlStatuses as $i => $wlStatus) {
                $startedAt = (clone $miniTask->created_at)->modify('+' . ($i + 1) . ' days');
                $completedAt = (clone $startedAt)->modify('+' . rand(120, 480) . ' minutes');
                $reviewedAt = $wlStatus === WorkLogStatus::APPROVED
                    ? (clone $completedAt)->modify('+1 day')
                    : null;

                $workLog = WorkLog::create([
                    'mini_task_id'    => $miniTask->id,
                    'started_at'      => $startedAt,
                    'completed_at'    => $completedAt,
                    'description'     => $workLogDescriptions[array_rand($workLogDescriptions)],
                    'status'          => $wlStatus->value,
                    'reviewed_by'     => $reviewerPool->isNotEmpty() && $wlStatus === WorkLogStatus::APPROVED
                        ? $reviewerPool->random()->id
                        : null,
                    'reviewed_at'     => $reviewedAt,
                    'created_at'      => $startedAt,
                    'updated_at'      => $completedAt,
                ]);

                $this->attachWorkers($workLog, $miniTask, $workers, $startedAt);
                $this->attachMaterials($workLog, $materials, $startedAt);
            }

            // Create one extra IN_PROGRESS work log
            if ($extraInProgress && $miniTask->status === MiniTaskStatus::IN_PROGRESS) {
                $extraInProgress = false;
                $startedAt = (clone $miniTask->created_at)->modify('+1 days');
                $workLog = WorkLog::create([
                    'mini_task_id'    => $miniTask->id,
                    'started_at'      => $startedAt,
                    'completed_at'    => null,
                    'description'     => 'Trabalho em curso — registo parcial do turno da manhã',
                    'status'          => WorkLogStatus::IN_PROGRESS->value,
                    'reviewed_by'     => null,
                    'reviewed_at'     => null,
                    'created_at'      => $startedAt,
                    'updated_at'      => $startedAt,
                ]);
                $this->attachWorkers($workLog, $miniTask, $workers, $startedAt);
            }

            // Create one REJECTED work log
            if ($extraRejected && $miniTask->status === MiniTaskStatus::COMPLETED) {
                $extraRejected = false;
                $startedAt = (clone $miniTask->created_at)->modify('+1 days');
                $completedAt = (clone $startedAt)->modify('+4 hours');
                $workLog = WorkLog::create([
                    'mini_task_id'    => $miniTask->id,
                    'started_at'      => $startedAt,
                    'completed_at'    => $completedAt,
                    'description'     => 'Trabalho rejeitado — não conforme com as especificações técnicas',
                    'status'          => WorkLogStatus::REJECTED->value,
                    'reviewed_by'     => $reviewerPool->first()?->id,
                    'reviewed_at'     => (clone $completedAt)->modify('+2 hours'),
                    'created_at'      => $startedAt,
                    'updated_at'      => $completedAt,
                ]);
                $this->attachWorkers($workLog, $miniTask, $workers, $startedAt);
            }
        }
    }

    private function attachWorkers(WorkLog $workLog, MiniTask $miniTask, $workers, $startedAt): void
    {
        $assignedWorkers = $miniTask->workers()->exists()
            ? $miniTask->workers()->inRandomOrder()->take(min(2, $miniTask->workers()->count()))->get()
            : $workers->random(min(2, $workers->count()));

        foreach ($assignedWorkers as $worker) {
            DB::table('work_logs_workers')->insert([
                'work_log_id' => $workLog->id,
                'worker_id'   => $worker->id,
                'created_at'  => $startedAt,
                'updated_at'  => $startedAt,
            ]);
        }
    }

    private function attachMaterials(WorkLog $workLog, $materials, $startedAt): void
    {
        if ($materials->isEmpty() || rand(1, 100) > 40) {
            return;
        }

        $usedMaterials = $materials->random(min(2, $materials->count()));

        foreach ($usedMaterials as $material) {
            DB::table('work_logs_materials')->insert([
                'id'              => Str::uuid(),
                'work_log_id'     => $workLog->id,
                'material_id'     => $material->id,
                'quantity_used'   => round(rand(10, 500) / 10, 1),
                'unit_price_at_use' => round(rand(50, 5000) / 100, 2),
                'created_at'      => $startedAt,
                'updated_at'      => $startedAt,
            ]);
        }
    }
}
