<?php

namespace Database\Factories;

use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Models\MiniTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MiniTask>
 */
class MiniTaskFactory extends Factory
{
    protected $model = MiniTask::class;

    private const DESCRIPTIONS = [
        'Transportar materiais e equipamentos para o local de intervenção',
        'Preparar e organizar a zona de trabalho com delimitação de segurança',
        'Executar corte e demolição necessária conforme especificações técnicas',
        'Aplicar camada de base e nivelamento para preparação da superfície',
        'Realizar medições e marcações de acordo com o projeto',
        'Efetuar ligações elétricas e testes de continuidade',
        'Testar funcionamento do sistema e verificar parâmetros operacionais',
        'Instalar equipamentos e acessórios conforme manual técnico',
        'Efetuar reparação localizada de danos identificados na vistoria',
        'Fazer limpeza final da área e remoção de resíduos de obra',
    ];

    public function definition(): array
    {
        return [
            // task_id, supervisor_id must be provided via state()
            'description' => fake()->randomElement(self::DESCRIPTIONS),
            'status'      => fake()->randomElement(MiniTaskStatus::cases())->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $a) => ['status' => MiniTaskStatus::PENDING->value]);
    }

    public function inProgress(): static
    {
        return $this->state(fn(array $a) => ['status' => MiniTaskStatus::IN_PROGRESS->value]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $a) => ['status' => MiniTaskStatus::COMPLETED->value]);
    }
}
