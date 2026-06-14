<?php

namespace Database\Seeders;

use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\TaskStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;
use App\Features\Workers\Models\Worker;
use App\Features\Teams\Models\Team;
use Illuminate\Database\Seeder;

class MiniTaskSeeder extends Seeder
{
    /**
     * Create mini-tasks covering every MiniTaskStatus.
     * supervisor_id points to the task_manager (Gestor de Tarefa) per UC1.
     */
    public function run(): void
    {
        $tasks       = Task::all();
        $taskManager = User::whereHas('roles', fn($q) => $q->where('name', 'task_manager'))->first();
        $manager     = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
        $workers     = Worker::all();
        $teams       = Team::all();

        if ($tasks->isEmpty() || (!$taskManager && !$manager)) {
            return;
        }

        $supervisorId = $taskManager?->id ?? $manager->id;

        $miniTaskPool = [
            'Transportar materiais e equipamentos para o local de intervenção',
            'Preparar e delimitar a zona de trabalho com sinalização de segurança',
            'Executar corte e demolição conforme especificações técnicas do projeto',
            'Aplicar camada de base e proceder ao nivelamento da superfície',
            'Realizar medições e marcações de acordo com o projeto aprovado',
            'Efetuar ligações e testes de funcionamento do sistema instalado',
            'Instalar equipamentos e acessórios conforme manual técnico',
            'Verificar parâmetros e validar conformidade com as especificações',
            'Efetuar reparação localizada dos danos identificados na inspeção',
            'Fazer limpeza final da área e remoção de resíduos de obra',
        ];

        /**
         * UC1 mini-task statuses: pending, in_progress, completed.
         * Maps each task status to a realistic set of child mini-task statuses.
         */
        $statusMap = [
            TaskStatus::PENDING->value            => [MiniTaskStatus::PENDING],
            TaskStatus::IN_PROGRESS->value        => [MiniTaskStatus::COMPLETED, MiniTaskStatus::IN_PROGRESS, MiniTaskStatus::PENDING],
            TaskStatus::AWAITING_APPROVAL->value  => [MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED],
            TaskStatus::COMPLETED->value          => [MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED],
            TaskStatus::CANCELLED->value          => [MiniTaskStatus::CANCELLED],
        ];

        foreach ($tasks as $task) {
            $mStatuses = $statusMap[$task->status->value] ?? [MiniTaskStatus::PENDING];

            foreach ($mStatuses as $i => $mStatus) {
                $desc      = $miniTaskPool[$i] ?? $miniTaskPool[array_rand($miniTaskPool)];
                $createdAt = (clone $task->created_at)->modify('+' . ($i + 1) . ' days');

                // Datas coerentes com o estado da micro-tarefa (campos adicionados em 2026_05_16_000002)
                $startDate = $mStatus !== MiniTaskStatus::PENDING
                    ? $createdAt->format('Y-m-d')
                    : null;

                $endDate = match ($mStatus) {
                    MiniTaskStatus::COMPLETED, MiniTaskStatus::CANCELLED => (clone $createdAt)->modify('+1 day')->format('Y-m-d'),
                    default                                              => null,
                };

                $miniTask = MiniTask::create([
                    'task_id'       => $task->id,
                    'supervisor_id' => $supervisorId,
                    'description'   => $desc,
                    'status'        => $mStatus->value,
                    'start_date'    => $startDate,
                    'end_date'      => $endDate,
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                ]);

                $this->assignResources($miniTask, $workers, $teams);
            }
        }
    }

    private function assignResources(MiniTask $miniTask, $workers, $teams): void
    {
        $assignWorkers = $miniTask->status === MiniTaskStatus::COMPLETED->value
            || (bool)($miniTask->id && ord($miniTask->id[0]) % 2 === 0);

        if ($assignWorkers && $workers->isNotEmpty()) {
            $numWorkers = min(3, $workers->count());
            $miniTask->workers()->sync($workers->random($numWorkers)->pluck('id')->toArray());
        } elseif ($teams->isNotEmpty()) {
            $miniTask->teams()->sync([$teams->random()->id]);
        }
    }
}
