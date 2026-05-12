<?php

namespace Database\Seeders;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Features\Tasks\Models\Task;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Create tasks covering every TaskStatus for the Testing Gallery.
     * Maps tasks to SOs by their status — e.g., a COMPLETED SO gets at least
     * one COMPLETED task and one BLOCKED task to test edge cases.
     */
    public function run(): void
    {
        $orders = ServiceOrder::all();
        $manager = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();

        if ($orders->isEmpty() || !$manager) {
            return;
        }

        // We create tasks deterministically for each SO based on its status
        // Each SO gets tasks that demonstrate valid child statuses for its state

        $taskDefs = [
            // PENDING SO → only pending tasks
            'pending' => [
                ['description' => 'Inspeção e levantamento de necessidades',          'status' => TaskStatus::PENDING],
                ['description' => 'Preparação do local de intervenção',               'status' => TaskStatus::PENDING],
            ],
            // IN_PROGRESS SO → mix of completed, in_progress, pending
            'in_progress' => [
                ['description' => 'Inspeção e levantamento de necessidades',          'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',               'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',              'status' => TaskStatus::IN_PROGRESS],
                ['description' => 'Aplicação de materiais e revestimentos',           'status' => TaskStatus::PENDING],
                ['description' => 'Sinalização e segurança do local',                 'status' => TaskStatus::BLOCKED],
            ],
            // COMPLETED SO → all tasks completed, plus one blocked for edge case
            'completed' => [
                ['description' => 'Inspeção e levantamento de necessidades',          'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',               'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',              'status' => TaskStatus::COMPLETED],
                ['description' => 'Aplicação de materiais e revestimentos',           'status' => TaskStatus::COMPLETED],
                ['description' => 'Controlo de qualidade e conformidade',             'status' => TaskStatus::COMPLETED],
                ['description' => 'Acabamentos e remates finais',                     'status' => TaskStatus::COMPLETED],
                ['description' => 'Vistoria final e elaboração de relatório',         'status' => TaskStatus::BLOCKED],
            ],
            // CANCELLED SO → all tasks cancelled
            'cancelled' => [
                ['description' => 'Inspeção e levantamento de necessidades',          'status' => TaskStatus::CANCELLED],
                ['description' => 'Preparação do local de intervenção',               'status' => TaskStatus::CANCELLED],
            ],
        ];

        $descriptions = [
            TaskStatus::PENDING->value     => 'Aguardando início dos trabalhos. Recursos serão alocados conforme disponibilidade.',
            TaskStatus::IN_PROGRESS->value => 'Trabalhos em curso conforme especificações técnicas do projeto.',
            TaskStatus::COMPLETED->value   => 'Tarefa concluída com sucesso. Conforme verificado pela equipa técnica.',
            TaskStatus::BLOCKED->value     => 'Trabalhos interrompidos devido a condições externas. Aguarda decisão.',
            TaskStatus::CANCELLED->value   => 'Tarefa cancelada por decisão superior. Sem impacto no cronograma geral.',
        ];

        foreach ($orders as $order) {
            // Determine which task set to use based on SO status
            $key = match ($order->status->value) {
                'pending'     => 'pending',
                'in_progress' => 'in_progress',
                'completed'   => 'completed',
                'cancelled'   => 'cancelled',
                default       => 'pending',
            };

            $defs = $taskDefs[$key];
            $assignee = $order->manager_id === $admin?->id ? $admin : $manager;

            foreach ($defs as $i => $def) {
                $isLater = $i + 1;
                $createdAt = (clone $order->created_at)->modify('+' . $isLater . ' days');

                Task::create([
                    'service_order_id' => $order->id,
                    'manager_id'       => $assignee?->id ?? $manager->id,
                    'description'      => $def['description'],
                    'status'           => $def['status']->value,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);
            }
        }
    }
}
