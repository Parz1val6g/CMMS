<?php

namespace Database\Factories;

use App\Features\Workers\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worker>
 */
class WorkerFactory extends Factory
{
    protected $model = Worker::class;

    public function definition(): array
    {
        return [
            // user_id and team_id must be provided via state() or seeder
            'cost_per_hour' => $this->faker->randomFloat(2, 10, 50),
        ];
    }
}
