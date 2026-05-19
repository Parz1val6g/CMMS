<?php

namespace Database\Factories;

use App\Features\Locations\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    private const STREETS = [
        'Rua da Liberdade',
        'Avenida da República',
        'Rua 25 de Abril',
        'Rua do Comércio',
        'Travessa da Fonte',
        'Rua Direita',
        'Largo da Igreja',
        'Avenida dos Combatentes',
        'Rua das Flores',
        'Rua da Paz',
        'Praça do Município',
        'Rua Nova',
        'Rua Dr. António José de Almeida',
        'Avenida Alberto Sampaio',
        'Rua da Sé',
        'Rua do Parque',
        'Avenida D. Duarte',
        'Rua dos Loureiros',
        'Estrada Nacional',
        'Avenida dos Bombeiros',
    ];

    private const LANDMARKS = [
        'Junto à Câmara Municipal',
        'Centro da Cidade',
        'Parque Central',
        'Zona Comercial',
        'Perto da Igreja Matriz',
        'Bairro Residencial',
        'Zona Industrial',
        'Junto ao Mercado Municipal',
        'Centro Histórico',
        'Frente ao Jardim Público',
        'Quartel dos Bombeiros Voluntários',
        'Escola Secundária',
        'Estação de Comboios',
        'Hospital Distrital',
        'Saída para a Autoestrada',
        'Junto à Ponte Medieval',
        'Praça Central',
        'Largo da Fonte Velha',
        'Entrada do Parque Industrial',
        'Sé Catedral',
    ];

    public function definition(): array
    {
        return [
            // parish_id must be provided via state() or seeder
            'postal_code'    => sprintf('%04d-%03d', fake()->numberBetween(1000, 9999), fake()->numberBetween(1, 999)),
            'street_address' => fake()->randomElement(self::STREETS) . ', nº ' . fake()->numberBetween(1, 500),
            'landmark'       => fake()->randomElement(self::LANDMARKS),
            'latitude'       => fake()->latitude(36.9, 42.2),
            'longitude'      => fake()->longitude(-9.5, -6.2),
        ];
    }
}
