<?php

namespace Database\Factories;

use App\Features\Sectors\Models\Sector;
use App\Shared\Models\User;
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
            'name' => 'Departamento ' . fake()->word(),
            'head_id' => User::factory(),
        ];
    }
}
