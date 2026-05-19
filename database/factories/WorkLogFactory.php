<?php

namespace Database\Factories;

use App\Core\Enums\WorkLogStatus;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkLog>
 */
class WorkLogFactory extends Factory
{
    protected $model = WorkLog::class;

    private const DESCRIPTIONS = [
        'Execução dos trabalhos conforme planeado e dentro do prazo estipulado',
        'Trabalho concluído com qualidade e dentro do tempo previsto',
        'Necessário material adicional para conclusão da tarefa',
        'Conclusão antecipada dos trabalhos face ao cronograma',
        'Intervenção concluída com sucesso após adaptação de procedimentos',
        'Trabalho em curso — registo parcial do turno da manhã',
        'Trabalho rejeitado — não conforme com as especificações técnicas',
        'Execução condicionada por condições meteorológicas adversas',
        'Trabalho realizado com equipamento de recurso por avaria do principal',
        'Turno concluído com todas as operações previstas executadas',
    ];

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', '-1 day');
        $end = (clone $start)->modify('+' . fake()->numberBetween(1, 8) . ' hours');

        return [
            // mini_task_id must be provided via state()
            'started_at'   => $start,
            'completed_at' => $end,
            'description'  => fake()->randomElement(self::DESCRIPTIONS),
            'status'       => fake()->randomElement(WorkLogStatus::cases())->value,
            'reviewed_by'  => null,
            'reviewed_at'  => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => WorkLogStatus::APPROVED->value,
            'reviewed_by' => null,
            'reviewed_at' => now(),
        ]);
    }
}
