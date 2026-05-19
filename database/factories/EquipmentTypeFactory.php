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

    private const TYPES = [
        'Veículo'              => 'vehicle',
        'Gerador'              => 'general',
        'Compressor'           => 'general',
        'Betoneira'            => 'general',
        'Andaime'              => 'general',
        'Martelo Pneumático'   => 'general',
        'Bomba de Água'        => 'general',
        'Vibrador de Placas'   => 'general',
        'Grua'                 => 'vehicle',
        'Escavadora'           => 'vehicle',
        'Retroescavadora'      => 'vehicle',
        'Camião'               => 'vehicle',
        'Cortadora de Asfalto' => 'general',
        'Perfuradora'          => 'general',
        'Serra Circular'       => 'general',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(self::TYPES));

        return [
            'name'     => $name,
            'category' => self::TYPES[$name],
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
