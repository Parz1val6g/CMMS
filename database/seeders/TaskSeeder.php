<?php

namespace Database\Seeders;

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
         * Each SO status maps to a realistic set of child task statuses.
         */
        $taskDefs = [
            // PENDING SO → only pending tasks (manager hasn't activated yet)
            'pending' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::PENDING],
                ['description' => 'Preparação do local e mobilização de recursos',       'status' => TaskStatus::PENDING],
            ],
            // IN_PROGRESS SO → mix of completed, awaiting_approval, in_progress, pending
            'in_progress' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::IN_PROGRESS],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::PENDING],
                ['description' => 'Sinalização e segurança do local',                   'status' => TaskStatus::AWAITING_APPROVAL],
            ],
            // AWAITING_APPROVAL SO → all tasks completed (triggers SO cascade to awaiting_approval)
            'awaiting_approval' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::COMPLETED],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::COMPLETED],
                ['description' => 'Sinalização temporária removida e local entregue',    'status' => TaskStatus::COMPLETED],
            ],
            // COMPLETED SO → all tasks completed, one cancelled as edge case
            'completed' => [
                ['description' => 'Inspeção e levantamento de necessidades no local',    'status' => TaskStatus::COMPLETED],
                ['description' => 'Preparação do local de intervenção',                  'status' => TaskStatus::COMPLETED],
                ['description' => 'Execução de trabalhos preparatórios',                 'status' => TaskStatus::COMPLETED],
                ['description' => 'Aplicação de materiais e revestimentos',              'status' => TaskStatus::COMPLETED],
                ['description' => 'Controlo de qualidade e conformidade',                'status' => TaskStatus::COMPLETED],
                ['description' => 'Acabamentos e remates finais',                        'status' => TaskStatus::COMPLETED],
                ['description' => 'Vistoria final e elaboração de relatório',            'status' => TaskStatus::CANCELLED],
            ],
            // CANCELLED SO → all tasks cancelled
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

            $defs         = $taskDefs[$key];
            $sectorIds    = $order->sectors->pluck('id');

            foreach ($defs as $i => $def) {
                $createdAt = (clone $order->created_at)->modify('+' . ($i + 1) . ' days');

                $task = Task::create([
                    'service_order_id' => $order->id,
                    'manager_id'       => $assignee->id,
                    'description'      => $def['description'],
                    'status'           => $def['status']->value,
                    'created_at'       => $createdAt,
                    'updated_at'       => $createdAt,
                ]);

                // Link to sector — UC1: one task per sector on activation
                if ($sectorIds->isNotEmpty()) {
                    $task->sectors()->sync([$sectorIds->first()]);
                }
            }
        }
    }
}
