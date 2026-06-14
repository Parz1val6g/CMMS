<?php

namespace Database\Seeders;

use App\Core\Enums\Priority;
use App\Core\Enums\TaskStatus;
use App\Features\Tasks\Models\Task;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Create tasks covering every TaskStatus for the Testing Gallery.
     * Each task is assigned to the task_manager (Gestor de Tarefa) per UC1.
     * Tasks are linked to the SO's sector via tasks_sectors.
     */
    public function run(): void
    {
        $orders      = ServiceOrder::with('sectors')->get();
        $taskManager = User::whereHas('roles', fn($q) => $q->where('name', 'task_manager'))->first();
        $manager     = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();

        if ($orders->isEmpty() || (!$taskManager && !$manager)) {
            return;
        }

        $assignee = $taskManager ?? $manager;

        /**
         * UC1 task statuses: pending, in_progress, awaiting_approval, completed, cancelled.
         * Cada estado de OS mapeia para um conjunto realista de tarefas filho.
         *
         * Campos adicionados pelas migrações:
         * - priority (2026_06_02_100002): nullable, herdado da prioridade da OS
         * - start_date / end_date (2026_05_29_000002): nullable
         * - taskable_id / taskable_type (2026_05_15_000003): morph polimórfico
         */
        $taskDefs = [
            // OS PENDENTE → só tarefas pendentes (gestor ainda não ativou)
            'pending' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::PENDING],
                ['description' => 'Preparação do local e mobilização de recursos',       'status' => TaskStatus::PENDING],
            ],
            // OS EM CURSO → mix de completadas, em aprovação, em curso, pendentes
            'in_progress' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::IN_PROGRESS],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::PENDING],
                ['description' => 'Sinalização e segurança do local',                   'status' => TaskStatus::AWAITING_APPROVAL],
            ],
            // OS AGUARDA APROVAÇÃO → todas as tarefas concluídas
            'awaiting_approval' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::COMPLETED],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::COMPLETED],
                ['description' => 'Sinalização temporária removida e local entregue',    'status' => TaskStatus::COMPLETED],
            ],
            // OS CONCLUÍDA → todas concluídas, uma cancelada como caso extremo
            'completed' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::COMPLETED],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::COMPLETED],
                ['description' => 'Controlo de qualidade e conformidade',                'status' => TaskStatus::COMPLETED],
                ['description' => 'Acabamentos e remates finais',                        'status' => TaskStatus::COMPLETED],
                ['description' => 'Vistoria final e elaboração de relatório',            'status' => TaskStatus::CANCELLED],
            ],
            // OS CANCELADA → todas as tarefas canceladas
            'cancelled' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::CANCELLED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::CANCELLED],
            ],
        ];

        foreach ($orders as $order) {
            $key = match ($order->status->value) {
                'pending'            => 'pending',
                'in_progress'        => 'in_progress',
                'awaiting_approval'  => 'awaiting_approval',
                'completed'          => 'completed',
                'cancelled'          => 'cancelled',
                default              => 'pending',
            };

            $defs      = $taskDefs[$key];
            $sectorIds = $order->sectors->pluck('id');

            // Herdar prioridade da OS para as tarefas (campo adicionado em 2026_06_02_100002)
            $priority = $order->priority instanceof Priority
                ? $order->priority->value
                : $order->priority;

            foreach ($defs as $i => $def) {
                $createdAt = (clone $order->created_at)->modify('+' . ($i + 1) . ' days');

                $startDate = $createdAt->format('Y-m-d');

                $endDate = match ($def['status']) {
                    TaskStatus::COMPLETED, TaskStatus::CANCELLED  => (clone $createdAt)->modify('+2 days')->format('Y-m-d'),
                    TaskStatus::AWAITING_APPROVAL                 => (clone $createdAt)->modify('+1 day')->format('Y-m-d'),
                    default                                       => (clone $createdAt)->modify('+7 days')->format('Y-m-d'),
                };

                $task = Task::create([
                    'service_order_id' => $order->id,
                    // Colunas polimórficas (adicionadas em 2026_05_15_000003)
                    'taskable_id'      => $order->id,
                    'taskable_type'    => ServiceOrder::class,
                    'manager_id'       => $assignee->id,
                    'description'      => $def['description'],
                    'status'           => $def['status']->value,
                    'priority'         => $priority,
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);

                // Ligar ao setor — UC1: uma tarefa por setor na ativação
                if ($sectorIds->isNotEmpty()) {
                    $task->sectors()->sync([$sectorIds->first()]);
                }
            }
        }
    }
}
