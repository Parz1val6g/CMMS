<?php

namespace Database\Seeders;

use App\Core\Enums\TaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $orders = DB::table('service_orders')->get();
        $managers = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager']);
                    });
            })->get();

        if ($orders->isEmpty() || $managers->isEmpty()) {
            return;
        }

        $taskNames = [
            'Inspeção e levantamento de necessidades',
            'Preparação do local de intervenção',
            'Execução de trabalhos preparatórios',
            'Aplicação de materiais',
            'Controlo de qualidade',
            'Acabamentos e remates',
            'Limpeza do local após intervenção',
            'Vistoria final e relatório',
            'Mobilização de equipamentos',
            'Sinalização e segurança do local',
        ];

        $statusMap = [
            'completed' => [TaskStatus::COMPLETED->value],
            'in_progress' => [TaskStatus::IN_PROGRESS->value, TaskStatus::PENDING->value, TaskStatus::BLOCKED->value],
            'pending' => [TaskStatus::PENDING->value],
            'cancelled' => [TaskStatus::CANCELLED->value],
        ];

        foreach ($orders as $order) {
            $numTasks = rand(2, 5);

            for ($i = 0; $i < $numTasks; $i++) {
                $manager = $managers->random();

                // Determine task status based on service order status
                $taskStatus = match ($order->status) {
                    'completed' => TaskStatus::COMPLETED->value,
                    'in_progress' => rand(0, 2) === 0 ? TaskStatus::COMPLETED->value : fake()->randomElement([TaskStatus::IN_PROGRESS->value, TaskStatus::PENDING->value]),
                    'pending' => TaskStatus::PENDING->value,
                    'cancelled' => TaskStatus::CANCELLED->value,
                    default => TaskStatus::PENDING->value,
                };

                DB::table('tasks')->insert([
                    'id' => Str::uuid(),
                    'service_order_id' => $order->id,
                    'manager_id' => $manager->id,
                    'name' => $taskNames[array_rand($taskNames)],
                    'status' => $taskStatus,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
            }
        }
    }
}
