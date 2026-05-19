<?php

namespace Database\Factories;

use App\Core\Enums\TaskStatus;
use App\Features\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    private const DESCRIPTIONS = [
        'Inspeção e levantamento de necessidades no local de intervenção',
        'Preparação do local de intervenção com delimitação de segurança',
        'Execução de trabalhos preparatórios conforme plano de obra',
        'Transporte de materiais e equipamentos para o local',
        'Aplicação de materiais e revestimentos de acordo com projeto',
        'Sinalização e segurança do local durante a intervenção',
        'Controlo de qualidade e verificação de conformidade técnica',
        'Acabamentos e remates finais dos trabalhos executados',
        'Limpeza da área de intervenção e remoção de resíduos',
        'Vistoria final e elaboração de relatório técnico',
        'Execução de corte e demolição controlada',
        'Montagem de estruturas de suporte e fixação',
        'Instalação de equipamentos e acessórios conforme manual',
        'Testes de funcionamento e verificação de parâmetros',
        'Reparação localizada de danos estruturais identificados',
    ];

    public function definition(): array
    {
        return [
            // service_order_id, taskable_id, taskable_type, manager_id must be provided via state()
            'description' => fake()->randomElement(self::DESCRIPTIONS),
            'status'      => fake()->randomElement(TaskStatus::cases())->value,
        ];
    }

    public function withManager(\App\Shared\Models\User $user): static
    {
        return $this->state(fn(array $a) => ['manager_id' => $user->id]);
    }

    public function pending(): static
    {
        return $this->state(fn(array $a) => ['status' => TaskStatus::PENDING->value]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $a) => ['status' => TaskStatus::IN_PROGRESS->value]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $a) => ['status' => TaskStatus::COMPLETED->value]);
    }
}
