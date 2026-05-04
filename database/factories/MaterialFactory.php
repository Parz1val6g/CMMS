<?php

namespace Database\Factories;

use App\Features\Materials\Models\Material;
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
            // unit_id must be provided via state() or seeder
            'name' => fake()->unique()->word(),
            'stock_quantity' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
