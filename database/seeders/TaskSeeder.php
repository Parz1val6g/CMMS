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
    private const NAMES = [
        'Inspeção e levantamento de necessidades',
        'Preparação do local de intervenção',
        'Execução de trabalhos preparatórios',
        'Aplicação de materiais e revestimentos',
        'Controlo de qualidade e conformidade',
        'Acabamentos e remates finais',
        'Limpeza do local após intervenção',
        'Vistoria final e elaboração de relatório',
        'Mobilização e desmobilização de equipamentos',
        'Sinalização e segurança do local',
    ];

    private const DESCRIPTIONS = [
        'Realizar inspeção detalhada ao local, identificando necessidades técnicas, materiais necessários e condições de segurança.',
        'Preparar a área de intervenção: isolamento, proteção de superfícies e montagem de andaimes ou estruturas de apoio.',
        'Executar os trabalhos preparatórios conforme especificações técnicas do projeto e normas aplicáveis.',
        'Aplicar os materiais especificados no projeto, garantindo a correta execução e cumprimento de prazos.',
        'Verificar a conformidade dos trabalhos executados com as especificações técnicas e normas de qualidade.',
        'Realizar acabamentos finais, correções de detalhes e preparação para entrega do trabalho ao cliente.',
        'Efetuar limpeza completa do local, remoção de entulhos e resíduos, deixando o espaço em condições de uso.',
        'Realizar vistoria final com a equipa técnica, documentar trabalhos concluídos e elaborar relatório de execução.',
        'Coordenar a mobilização de equipamentos e recursos necessários, garantindo logística eficiente no local.',
        'Implementar sinalização de segurança, delimitar área de trabalho e garantir conformidade com normas de segurança.',
    ];

    public function run(): void
    {
        $orders = ServiceOrder::all();
        $managers = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();

        if ($orders->isEmpty() || $managers->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            $numTasks = rand(2, 5);
            $assigned = [];
            $orderStatus = $order->status;

            for ($i = 0; $i < $numTasks; $i++) {
                $name = self::NAMES[array_rand(self::NAMES)];

                // Avoid duplicate task names for same SO
                while (in_array($name, $assigned)) {
                    $name = self::NAMES[array_rand(self::NAMES)];
                }
                $assigned[] = $name;

                $status = match ($orderStatus) {
                    ServiceOrderStatus::COMPLETED => TaskStatus::COMPLETED,
                    ServiceOrderStatus::IN_PROGRESS => fake()->randomElement([
                        TaskStatus::COMPLETED, TaskStatus::IN_PROGRESS, TaskStatus::PENDING,
                    ]),
                    ServiceOrderStatus::PENDING => TaskStatus::PENDING,
                    ServiceOrderStatus::CANCELLED => TaskStatus::CANCELLED,
                    default => TaskStatus::PENDING,
                };

                Task::create([
                    'service_order_id' => $order->id,
                    'manager_id' => $managers->random()->id,
                    'name' => $name,
                    'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                    'status' => $status->value,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
        }
    }
}
