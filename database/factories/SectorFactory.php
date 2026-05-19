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

    private const POOL = [
        'Departamento de Obras e Viação',
        'Departamento de Urbanismo',
        'Departamento de Limpeza Urbana',
        'Departamento de Água e Saneamento',
        'Departamento de Espaços Verdes',
        'Departamento de Obras Públicas',
        'Departamento de Infraestruturas',
        'Divisão de Manutenção e Logística',
        'Serviços Técnicos Municipais',
        'Gabinete de Apoio Técnico',
        'Departamento de Higiene e Limpeza',
        'Gabinete de Projetos e Obras',
        'Divisão de Equipamentos e Frotas',
        'Serviços de Gestão de Resíduos',
        'Gabinete Jurídico e de Contratação',
    ];

    public function definition(): array
    {
        return [
            // head_id must be provided via state() or seeder
            'name' => fake()->unique()->randomElement(self::POOL),
        ];
    }
}
