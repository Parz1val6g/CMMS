<?php

namespace Database\Factories;

use App\Features\Materials\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    private const UNITS = [
        'Quilograma'     => 'kg',
        'Metro'           => 'm',
        'Metro Linear'    => 'ml',
        'Metro Quadrado'  => 'm²',
        'Metro Cúbico'    => 'm³',
        'Litro'           => 'l',
        'Unidade'         => 'un',
        'Balde'           => 'bld',
        'Saco'            => 'sco',
        'Caixa'           => 'cx',
        'Rolo'            => 'rol',
        'Placa'           => 'plc',
        'Hora'            => 'h',
        'Dia'             => 'd',
        'Par'             => 'par',
        'Conjunto'        => 'cj',
        'Fardo'           => 'frd',
        'Palete'          => 'plt',
        'Lata'            => 'lt',
        'Bidão'           => 'bd',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(self::UNITS));

        return [
            'name'         => $name,
            'abbreviation' => self::UNITS[$name],
        ];
    }
}
