<?php

namespace Database\Factories;

use App\Features\Workers\Models\Worker;
use App\Shared\Models\User;
use App\Features\Teams\Models\Team;
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
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
