<?php

namespace Database\Factories;

use App\Core\Enums\EquipmentRevisionStatus;
use App\Features\Equipments\Models\Equipment;
use App\Features\Equipments\Models\EquipmentRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentRevision>
 */
class EquipmentRevisionFactory extends Factory
{
    protected $model = EquipmentRevision::class;

    public function definition(): array
    {
        return [
            'equipment_id'  => Equipment::factory()->create()->id,
            'status'        => EquipmentRevisionStatus::PENDING->value,
            'revision_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'notes'         => fake()->optional()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn(array $a) => [
            'status'      => EquipmentRevisionStatus::APPROVED->value,
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn(array $a) => [
            'status' => EquipmentRevisionStatus::PENDING->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $a) => [
            'status' => EquipmentRevisionStatus::REJECTED->value,
        ]);
    }
}
