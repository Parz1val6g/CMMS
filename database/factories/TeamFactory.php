<?php

namespace Database\Factories;

use App\Features\Teams\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            // sector_id must be provided via state() or seeder
            'name' => fake()->unique()->word(),
        ];
    }
}
