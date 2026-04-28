<?php

namespace Database\Factories;

use App\Features\Teams\Models\Team;
use App\Features\Sectors\Models\Sector;
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
            'sector_id' => Sector::factory(),
            'name' => 'Equipa ' . fake()->word(),
        ];
    }
}
