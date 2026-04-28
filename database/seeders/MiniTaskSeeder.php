<?php

namespace Database\Seeders;

use App\Core\Enums\MiniTaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MiniTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = DB::table('tasks')->get();
        $supervisors = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager', 'supervisor']);
                    });
            })->get();

        if ($tasks->isEmpty() || $supervisors->isEmpty()) {
            return;
        }

        $miniTaskDescriptions = [
            'Transportar materiais para o local',
            'Preparar a zona de trabalho',
            'Executar corte e demolição necessária',
            'Aplicar camada de base',
            'Realizar medições e nivelamento',
            'Montar estruturas de suporte',
            'Aplicar revestimento',
            'Efetuar ligações elétricas',
            'Testar funcionamento do sistema',
            'Verificar conformidade com o projeto',
            'Realizar soldaduras necessárias',
            'Aplicar pintura e proteção',
            'Instalar equipamentos',
            'Efetuar reparação localizada',
            'Fazer limpeza final da área',
        ];

        foreach ($tasks as $task) {
            $numMiniTasks = rand(1, 4);

            for ($i = 0; $i < $numMiniTasks; $i++) {
                $supervisor = $supervisors->random();

                $miniStatus = match ($task->status) {
                    'completed' => MiniTaskStatus::COMPLETED->value,
                    'in_progress' => fake()->randomElement([MiniTaskStatus::IN_PROGRESS->value, MiniTaskStatus::COMPLETED->value, MiniTaskStatus::PENDING->value]),
                    'pending' => MiniTaskStatus::PENDING->value,
                    'blocked' => MiniTaskStatus::BLOCKED->value,
                    'cancelled' => MiniTaskStatus::CANCELLED->value,
                    default => MiniTaskStatus::PENDING->value,
                };

                DB::table('mini_tasks')->insert([
                    'id' => Str::uuid(),
                    'task_id' => $task->id,
                    'supervisor_id' => $supervisor->id,
                    'description' => $miniTaskDescriptions[array_rand($miniTaskDescriptions)],
                    'status' => $miniStatus,
                    'created_at' => $task->created_at,
                    'updated_at' => $task->created_at,
                ]);
            }
        }
    }
}
