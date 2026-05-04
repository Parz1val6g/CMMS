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

    public function definition(): array
    {
        return [
            // task_id, supervisor_id must be provided via state()
            'description' => fake()->sentence(6),
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
