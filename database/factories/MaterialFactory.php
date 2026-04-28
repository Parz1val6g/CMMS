<?php

namespace Database\Factories;

use App\Features\Materials\Models\Material;
use App\Features\Materials\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'unit_id' => Unit::factory(),
            'stock_quantity' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
