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

    public function definition(): array
    {
        return [
            // service_order_id, manager_id must be provided via state()
            'name'   => fake()->sentence(4),
            'status' => fake()->randomElement(TaskStatus::cases())->value,
        ];
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
