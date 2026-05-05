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
     * Each mini-task gets deterministic resource assignments.
     */
    public function run(): void
    {
        $tasks = Task::all();
        $supervisor = User::whereHas('roles', fn($q) => $q->where('name', 'supervisor'))->first();
        $manager = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
        $workers = Worker::all();
        $teams = Team::all();

        if ($tasks->isEmpty() || (!$supervisor && !$manager)) {
            return;
        }

        $supervisorId = $supervisor?->id ?? $manager->id;

        $miniTaskPool = [
            'Transportar materiais e equipamentos para o local de intervenção',
            'Preparar e organizar a zona de trabalho com delimitação de segurança',
            'Executar corte e demolição necessária conforme especificações técnicas',
            'Aplicar camada de base e nivelamento para preparação da superfície',
            'Realizar medições e marcações de acordo com o projeto',
            'Efetuar ligações elétricas e testes de continuidade',
            'Testar funcionamento do sistema e verificar parâmetros',
            'Instalar equipamentos e acessórios conforme manual técnico',
            'Efetuar reparação localizada de danos identificados',
            'Fazer limpeza final da área e remoção de resíduos',
        ];

        /**
         * For each task status, we create mini-tasks that represent valid child states.
         * This ensures every MiniTaskStatus appears at least once.
         */
        $statusMap = [
            TaskStatus::PENDING->value     => [MiniTaskStatus::PENDING],
            TaskStatus::IN_PROGRESS->value => [MiniTaskStatus::COMPLETED, MiniTaskStatus::IN_PROGRESS, MiniTaskStatus::PENDING],
            TaskStatus::COMPLETED->value   => [MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED],
            TaskStatus::BLOCKED->value     => [MiniTaskStatus::BLOCKED, MiniTaskStatus::PENDING],
            TaskStatus::CANCELLED->value   => [MiniTaskStatus::CANCELLED],
        ];

        foreach ($tasks as $task) {
            $mStatuses = $statusMap[$task->status->value] ?? [MiniTaskStatus::PENDING];

            foreach ($mStatuses as $i => $mStatus) {
                $desc = $miniTaskPool[$i] ?? $miniTaskPool[array_rand($miniTaskPool)];
                $createdAt = (clone $task->created_at)->modify('+' . ($i + 1) . ' days');

                $miniTask = MiniTask::create([
                    'task_id'       => $task->id,
                    'supervisor_id' => $supervisorId,
                    'description'   => $desc,
                    'status'        => $mStatus->value,
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                ]);

                $this->assignResources($miniTask, $workers, $teams);
            }
        }
    }

    private function assignResources(MiniTask $miniTask, $workers, $teams): void
    {
        // Alternate: odd-indexed mini-tasks get workers, even-indexed get teams
        $assignWorkers = $miniTask->status === MiniTaskStatus::COMPLETED->value || (bool)($miniTask->id && ord($miniTask->id[0]) % 2 === 0);

        if ($assignWorkers && $workers->isNotEmpty()) {
            $numWorkers = min(3, $workers->count());
            $assignedWorkers = $workers->random($numWorkers);
            $miniTask->workers()->sync($assignedWorkers->pluck('id')->toArray());
        } elseif ($teams->isNotEmpty()) {
            $assignedTeam = $teams->random();
            $miniTask->teams()->sync([$assignedTeam->id]);
        }
    }
}
