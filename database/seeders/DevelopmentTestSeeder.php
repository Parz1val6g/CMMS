<?php

namespace Database\Seeders;

use App\Core\Enums\ServiceOrderStatus as SOStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkLogStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\Materials\Models\Material;
use App\Features\Equipments\Models\Equipment;
use App\Features\Workers\Models\Worker;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DevelopmentTestSeeder extends Seeder
{
    private const SO_TASK_NAMES = [
        'regular' => [
            'Inspeção e levantamento de necessidades',
            'Preparação do local de intervenção',
        ],
        'loan' => [
            'Empréstimo de Equipamento',
        ],
    ];

    private const MINI_TASK_POOL = [
        'Transportar materiais e equipamentos para o local de intervenção',
        'Preparar e organizar a zona de trabalho com delimitação de segurança',
        'Executar corte e demolição necessária conforme especificações técnicas',
        'Aplicar camada de base e nivelamento para preparação da superfície',
        'Realizar medições e marcações de acordo com o projeto',
        'Montar estruturas de suporte e fixação dos elementos',
        'Instalar equipamentos e acessórios conforme manual técnico',
        'Testar funcionamento do sistema e verificar parâmetros',
        'Efetuar reparação localizada de danos identificados',
        'Fazer limpeza final da área e remoção de resíduos',
    ];

    private const WORK_LOG_DESCRIPTIONS = [
        'Execução dos trabalhos conforme planeado e dentro do prazo estipulado',
        'Trabalho concluído com qualidade e dentro do tempo previsto',
        'Intervenção concluída com sucesso após adaptação de procedimentos',
        'Trabalho executado com equipamento próprio da equipa',
    ];

    public function run(): void
    {
        // ── Bail if reference data is missing ──
        $client      = Client::inRandomOrder()->first();
        $manager     = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->inRandomOrder()->first();
        $taskManager = User::whereHas('roles', fn($q) => $q->where('name', 'task_manager'))->first() ?? $manager;
        $location    = Location::inRandomOrder()->first();
        $workers     = Worker::inRandomOrder()->take(4)->get();
        $materials   = Material::inRandomOrder()->take(4)->get();
        $loanableEquipments = Equipment::where('is_loanable', true)->inRandomOrder()->take(2)->get();
        $reviewer    = $taskManager;

        if (!$client || !$manager || !$location || $workers->count() < 2 || $materials->count() < 2 || $loanableEquipments->count() < 1 || !$reviewer) {
            $this->command->error('❌ Missing reference data. Run DatabaseSeeder first.');
            return;
        }

        // ── Cleanup: reverse FK order ──
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('work_log_equipment')->delete();
        DB::table('work_logs_materials')->delete();
        DB::table('mini_tasks_materials')->delete();
        DB::table('work_logs_workers')->delete();
        DB::table('work_logs')->delete();
        DB::table('mini_tasks')->delete();
        DB::table('tasks')->delete();
        DB::table('service_orders')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $now = now();
        $this->command->info('🧹 Data cleaned — all target tables truncated.');

        // ── SO #1: workflow regular ──
        // service_type_id foi removido da tabela pela migração 2026_06_02_100005;
        // o tipo de serviço é agora associado via service_order_sector_service_type.
        $so1 = ServiceOrder::create([
            'process'     => 'OS/2026/DEV-001',
            'title'       => 'Reparação de pavimento — via pública (DEV)',
            'client_id'   => $client->id,
            'manager_id'  => $manager->id,
            'location_id' => $location->id,
            'priority'    => Priority::NORMAL->value,
            'start_date'  => $now->copy(),
            'end_date'    => $now->copy()->addDays(3),
            'status'      => SOStatus::IN_PROGRESS->value,
            'description' => 'Reparação de piso danificado na via pública com aplicação de nova camada de asfalto',
        ]);

        // ── SO #2: workflow de empréstimo ──
        $so2 = ServiceOrder::create([
            'process'     => 'OS/2026/DEV-002',
            'title'       => 'Empréstimo de equipamento — zona industrial (DEV)',
            'client_id'   => $client->id,
            'manager_id'  => $manager->id,
            'location_id' => $location->id,
            'priority'    => Priority::HIGH->value,
            'start_date'  => $now->copy(),
            'end_date'    => $now->copy()->addDays(1),
            'status'      => SOStatus::IN_PROGRESS->value,
            'description' => 'Instalação de equipamento em regime de comodato na zona industrial',
        ]);

        $this->command->info('✅ 2 Service Orders created (regular + loan).');

        // ── Tasks ──
        $taskStatusMap = [
            'regular' => [TaskStatus::COMPLETED, TaskStatus::IN_PROGRESS],
            'loan'    => [TaskStatus::IN_PROGRESS],
        ];

        $allTasks = [];
        foreach (['regular', 'loan'] as $type) {
            $so = $type === 'regular' ? $so1 : $so2;
            $names = self::SO_TASK_NAMES[$type];
            $statuses = $taskStatusMap[$type];

            foreach ($names as $i => $name) {
                $taskStatus = $statuses[$i];
                // Campos adicionados pelas migrações:
                // - taskable_id / taskable_type (2026_05_15_000003)
                // - priority (2026_06_02_100002)
                // - start_date / end_date (2026_05_29_000002)
                $startDate = $taskStatus !== TaskStatus::PENDING ? $now->toDateString() : null;
                $endDate   = $taskStatus === TaskStatus::COMPLETED ? $now->copy()->addDays(2)->toDateString() : null;

                $task = Task::create([
                    'service_order_id' => $so->id,
                    'taskable_id'      => $so->id,
                    'taskable_type'    => ServiceOrder::class,
                    'manager_id'       => $manager->id,
                    'description'      => "Execução de {$name} conforme especificações técnicas do projeto.",
                    'status'           => $taskStatus->value,
                    'priority'         => $so->priority instanceof Priority ? $so->priority->value : $so->priority,
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                ]);
                $allTasks[] = $task;
            }
        }
        $this->command->info('✅ 3 Tasks created (2 regular + 1 loan).');

        // ── Mini-tasks ──
        $miniTaskStatusMap = [
            TaskStatus::COMPLETED->value   => [MiniTaskStatus::COMPLETED, MiniTaskStatus::COMPLETED],
            TaskStatus::IN_PROGRESS->value => [MiniTaskStatus::IN_PROGRESS, MiniTaskStatus::PENDING],
        ];

        $allMiniTasks = [];
        foreach ($allTasks as $task) {
            // $task->status é um enum TaskStatus devido ao cast — usar ->value para aceder ao mapa
            $statuses = $miniTaskStatusMap[$task->status->value] ?? [MiniTaskStatus::PENDING, MiniTaskStatus::PENDING];
            $pool = self::MINI_TASK_POOL;
            $chosen = [];

            for ($i = 0; $i < 2; $i++) {
                $desc = $pool[array_rand($pool)];

                // Avoid duplicates within same task
                while (in_array($desc, $chosen)) {
                    $desc = $pool[array_rand($pool)];
                }
                $chosen[] = $desc;

                $mtStatus = $statuses[$i];
                // Campos adicionados pela migração 2026_05_16_000002
                $mtStartDate = $mtStatus !== MiniTaskStatus::PENDING ? $now->toDateString() : null;
                $mtEndDate   = $mtStatus === MiniTaskStatus::COMPLETED ? $now->copy()->addDay()->toDateString() : null;

                $mt = MiniTask::create([
                    'task_id'       => $task->id,
                    'supervisor_id' => $taskManager->id,
                    'description'   => $desc,
                    'status'        => $mtStatus->value,
                    'start_date'    => $mtStartDate,
                    'end_date'      => $mtEndDate,
                ]);
                $allMiniTasks[] = $mt;
            }
        }
        $this->command->info('✅ 8 Mini-tasks created (2 per Task).');

        // ── Work logs ──
        $workLogStatusMap = [
            MiniTaskStatus::COMPLETED->value   => WorkLogStatus::APPROVED,
            MiniTaskStatus::IN_PROGRESS->value => WorkLogStatus::SUBMITTED,
            MiniTaskStatus::PENDING->value     => null, // skip
        ];

        $allWorkLogs = [];
        foreach ($allMiniTasks as $mt) {
            $wlStatus = $workLogStatusMap[$mt->status] ?? null;
            if ($wlStatus === null) {
                continue; // pending mini-tasks get no work logs
            }

            for ($i = 0; $i < 2; $i++) {
                $startedAt = (clone $now)->subDays(rand(1, 5));
                $durationMinutes = rand(60, 480);
                $completedAt = (clone $startedAt)->addMinutes($durationMinutes);

                $wl = WorkLog::create([
                    'mini_task_id'    => $mt->id,
                    'started_at'      => $startedAt,
                    'completed_at'    => $completedAt,
                    'description'     => self::WORK_LOG_DESCRIPTIONS[array_rand(self::WORK_LOG_DESCRIPTIONS)],
                    'duration_minutes'=> $durationMinutes,
                    'status'          => $wlStatus->value,
                    'reviewed_by'     => $wlStatus === WorkLogStatus::APPROVED ? $reviewer->id : null,
                    'reviewed_at'     => $wlStatus === WorkLogStatus::APPROVED ? (clone $completedAt)->addDay() : null,
                ]);

                // Attach 2 workers
                $wlWorkers = $workers->random(2);
                foreach ($wlWorkers as $w) {
                    DB::table('work_logs_workers')->insert([
                        'work_log_id' => $wl->id,
                        'worker_id'   => $w->id,
                        'created_at'  => $startedAt,
                        'updated_at'  => $startedAt,
                    ]);
                }

                $allWorkLogs[] = $wl;
            }
        }
        $this->command->info('✅ Work logs created with worker assignments.');

        // ── Materials / Equipment per work log ──
        foreach ($allWorkLogs as $wl) {
            // Find which SO this work log belongs to
            $mt = MiniTask::find($wl->mini_task_id);
            $task = Task::find($mt->task_id);
            $so = ServiceOrder::find($task->service_order_id);

            if ($so->id === $so1->id) {
                // Attach 2 materials
                $usedMats = $materials->random(2);
                foreach ($usedMats as $mat) {
                    DB::table('work_logs_materials')->insert([
                        'id'              => Str::uuid(),
                        'work_log_id'     => $wl->id,
                        'material_id'     => $mat->id,
                        'unit_price_at_use' => round(rand(50, 5000) / 100, 2),
                        'quantity_used'   => round(rand(10, 500) / 10, 1),
                        'created_at'      => $wl->created_at,
                        'updated_at'      => $wl->created_at,
                    ]);
                }
            } else {
                // Attach 2 equipments (loan workflow)
                foreach ($loanableEquipments as $eq) {
                    DB::table('work_log_equipment')->insert([
                        'work_log_id'  => $wl->id,
                        'equipment_id' => $eq->id,
                        'created_at'   => $wl->created_at,
                        'updated_at'   => $wl->created_at,
                    ]);
                }
            }
        }
        $this->command->info('✅ Materials/Equipments linked to work logs.');

        // ── Summary ──
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Service Orders', 2],
                ['Tasks', count($allTasks)],
                ['Mini-tasks', count($allMiniTasks)],
                ['Work Logs', count($allWorkLogs)],
                ['Materials (pivot rows)', DB::table('work_logs_materials')->count()],
                ['Equipments (pivot rows)', DB::table('work_log_equipment')->count()],
                ['Workers assigned', DB::table('work_logs_workers')->count()],
            ]
        );

        $this->command->info('🎯 DevelopmentTestSeeder complete — ready for SOWorkspaceDrawer / SOTasksTree testing.');
    }
}
