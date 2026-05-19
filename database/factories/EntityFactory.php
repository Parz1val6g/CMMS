<?php

namespace Database\Factories;

use App\Core\Enums\EntityType;
use App\Features\Entities\Models\Entity;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
{
    protected $model = Entity::class;

    private const MUNICIPAL_NAMES = [
        'Câmara Municipal de Viseu',
        'Câmara Municipal de Mangualde',
        'Câmara Municipal de Gouveia',
        'Câmara Municipal de Seia',
        'Câmara Municipal de Nelas',
        'Câmara Municipal de Penalva do Castelo',
        'Câmara Municipal de Tondela',
        'Câmara Municipal de Oliveira do Hospital',
    ];

    private const PARISH_NAMES = [
        'Junta de Freguesia de São José',
        'Junta de Freguesia de Santa Maria',
        'Junta de Freguesia de Santiago',
        'Junta de Freguesia de São Pedro',
        'Junta de Freguesia de Abrunhosa-a-Velha',
        'Junta de Freguesia de Cunha Baixa',
        'Junta de Freguesia de Espinho',
        'Junta de Freguesia de Fornos de Maceira Dão',
        'Junta de Freguesia de Mesquitela',
        'Junta de Freguesia de Alcafache',
    ];

    public function definition(): array
    {
        $entityType = $this->faker->randomElement(EntityType::cases());
        $isMunicipal = $entityType === EntityType::MUNICIPAL_COUNCIL;

        return [
            'user_id'     => User::factory(),
            'entity_type' => $entityType->value,
            'nif'         => $this->faker->optional()->numerify('5########'),
            'name'        => $isMunicipal
                ? $this->faker->randomElement(self::MUNICIPAL_NAMES)
                : $this->faker->randomElement(self::PARISH_NAMES),
            'phone'       => $this->faker->optional()->numerify('+351 2## ### ###'),
            'location_id' => null,
        ];
    }

    public function municipalCouncil(): static
    {
        return $this->state(fn(array $a) => [
            'entity_type' => EntityType::MUNICIPAL_COUNCIL->value,
            'name'        => $this->faker->randomElement(self::MUNICIPAL_NAMES),
        ]);
    }

    public function parishCouncil(): static
    {
        return $this->state(fn(array $a) => [
            'entity_type' => EntityType::PARISH_COUNCIL->value,
            'name'        => $this->faker->randomElement(self::PARISH_NAMES),
        ]);
    }
}
