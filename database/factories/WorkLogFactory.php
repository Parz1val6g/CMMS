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

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', '-1 day');
        // Avoid DST spring-forward gap (Europe/Lisbon: Mar last Sun 01:00-02:00)
        if ($start->format('Y-m-d') === '2026-03-29' && $start->format('H') === '01') {
            $start->modify('+1 hour');
        }
        $end = (clone $start)->modify('+' . fake()->numberBetween(1, 8) . ' hours');

        return [
            // mini_task_id must be provided via state()
            'started_at'   => $start,
            'completed_at' => $end,
            'description'  => fake()->sentence(6),
            'status'       => fake()->randomElement(WorkLogStatus::cases())->value,
            'reviewed_by'  => null,
            'reviewed_at'  => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => WorkLogStatus::APPROVED->value,
            'reviewed_by' => null, // must be set by seeder
            'reviewed_at' => now(),
        ]);
    }
}
