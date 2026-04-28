<?php

namespace Database\Factories;

use App\Core\Enums\MiniTaskStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;
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
            'task_id' => Task::factory(),
            'supervisor_id' => User::factory(),
            'description' => fake()->sentence(6),
            'status' => fake()->randomElement(MiniTaskStatus::cases())->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $a) => ['status' => MiniTaskStatus::PENDING->value]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $a) => ['status' => MiniTaskStatus::COMPLETED->value]);
    }
}
