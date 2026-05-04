<?php

namespace Database\Factories;

use App\Features\Sectors\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sector>
 */
class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        return [
            // name and head_id must be provided via state() or seeder
            'name' => fake()->unique()->word(),
        ];
    }
}
