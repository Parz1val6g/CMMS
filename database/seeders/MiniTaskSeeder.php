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
    private const DESCRIPTIONS = [
        'Transportar materiais e equipamentos para o local de intervenção',
        'Preparar e organizar a zona de trabalho com delimitação de segurança',
        'Executar corte e demolição necessária conforme especificações técnicas',
        'Aplicar camada de base e nivelamento para preparação da superfície',
        'Realizar medições e marcações de acordo com o projeto',
        'Montar estruturas de suporte e fixação dos elementos',
        'Aplicar revestimento superficial com acabamento uniforme',
        'Efetuar ligações elétricas e testes de continuidade',
        'Testar funcionamento do sistema e verificar parâmetros',
        'Verificar conformidade com especificações do projeto',
        'Realizar soldaduras e uniões necessárias',
        'Aplicar pintura, proteção anticorrosiva e selante',
        'Instalar equipamentos e acessórios conforme manual técnico',
        'Efetuar reparação localizada de danos identificados',
        'Fazer limpeza final da área e remoção de resíduos',
        'Montar cofragem e preparar para betonagem',
        'Aplicar betão e proceder à vibração adequada',
        'Executar impermeabilização de superfícies horizontais',
        'Colocar isolamento térmico e acústico conforme projeto',
        'Proceder à selagem de juntas de dilatação',
    ];

    public function run(): void
    {
        $tasks = Task::all();
        $supervisors = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();
        $workers = Worker::all();
        $teams = Team::all();

        if ($tasks->isEmpty() || $supervisors->isEmpty()) {
            return;
        }

        foreach ($tasks as $task) {
            $numMiniTasks = rand(1, 4);
            $taskStatus = $task->status;

            for ($i = 0; $i < $numMiniTasks; $i++) {
                $status = $this->resolveStatus($taskStatus);

                $miniTask = MiniTask::create([
                    'task_id' => $task->id,
                    'supervisor_id' => $supervisors->random()->id,
                    'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                    'status' => $status->value,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->created_at,
                ]);

                $this->assignResources($miniTask, $workers, $teams);
            }
        }
    }

    private function resolveStatus(?TaskStatus $taskStatus): MiniTaskStatus
    {
        return match ($taskStatus) {
            TaskStatus::COMPLETED => MiniTaskStatus::COMPLETED,
            TaskStatus::IN_PROGRESS => fake()->randomElement([
                MiniTaskStatus::COMPLETED,
                MiniTaskStatus::IN_PROGRESS,
                MiniTaskStatus::PENDING,
            ]),
            TaskStatus::BLOCKED => fake()->randomElement([
                MiniTaskStatus::BLOCKED,
                MiniTaskStatus::PENDING,
            ]),
            TaskStatus::PENDING => MiniTaskStatus::PENDING,
            TaskStatus::CANCELLED => MiniTaskStatus::CANCELLED,
            default => MiniTaskStatus::PENDING,
        };
    }

    private function assignResources(MiniTask $miniTask, $workers, $teams): void
    {
        // 60% assign to individual workers, 40% assign to a team
        if (fake()->boolean(60) && $workers->isNotEmpty()) {
            $assignedWorkers = $workers->random(rand(2, min(3, $workers->count())));
            $miniTask->workers()->sync($assignedWorkers->pluck('id')->toArray());
        } elseif ($teams->isNotEmpty()) {
            $assignedTeam = $teams->random();
            $miniTask->teams()->sync([$assignedTeam->id]);
        }
    }
}
