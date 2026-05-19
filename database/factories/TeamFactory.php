<?php

namespace Database\Factories;

use App\Features\Teams\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    private const POOL = [
        'Equipa de Pavimentação',
        'Equipa de Sinalização',
        'Equipa de Calcetamento',
        'Equipa de Redes de Água',
        'Equipa de Saneamento',
        'Equipa de Recolha de Resíduos',
        'Equipa de Manutenção de Jardins',
        'Equipa de Limpeza de Vias',
        'Equipa de Licenciamento',
        'Equipa de Fiscalização Técnica',
        'Equipa de Eletricidade',
        'Equipa de Carpintaria',
        'Equipa de Serralharia',
        'Equipa de Pintura',
        'Equipa de Jardinagem',
        'Equipa de Obras de Arte',
        'Equipa de Topografia',
        'Equipa de Drenagem',
        'Equipa de Soldadura',
        'Equipa de Manutenção de Edifícios',
    ];

    public function definition(): array
    {
        return [
            // sector_id must be provided via state() or seeder
            'name' => fake()->unique()->randomElement(self::POOL),
        ];
    }
}
