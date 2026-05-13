<?php

namespace Database\Factories;

use App\Features\Equipments\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentType>
 */
class EquipmentTypeFactory extends Factory
{
    protected $model = EquipmentType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'category' => fake()->randomElement(['vehicle', 'general']),
        ];
    }

    public function vehicle(): static
    {
        return $this->state(fn(array $a) => ['category' => 'vehicle']);
    }

    public function general(): static
    {
        return $this->state(fn(array $a) => ['category' => 'general']);
    }
}
