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

    private const POOL = [
        'Cimento Portland CEM II 32,5N',
        'Areia de Rio Lavada',
        'Brita Calcária 12/25 mm',
        'Betão Betuminoso a Quente AC14',
        'Calçada de Granito 11x11 cm',
        'Tubo de PVC Corrugado DN200',
        'Tubo de PEAD DN90 PN10',
        'Condutor Elétrico Cu 4mm²',
        'Luminária LED 150W IP66',
        'Sinal de Trânsito Vertical 60cm',
        'Tinta de Sinalização Branca 25kg',
        'Barreira de Segurança Plástica',
        'Geotêxtil Não-Tecido 200g/m²',
        'Argamassa de Reparação Estrutural',
        'Lajeta de Betão 40x40x5 cm',
        'Vedante de Juntas de Silicone',
        'Perfil Metálico IPE 200',
        'Chapa de Aço Galvanizado 2mm',
        'Tinta Plástica Branca 15L',
        'Rede de Aço Electrossoldada AQ50',
        'Membrana Asfáltica de Impermeabilização',
        'Bloco Térmico 30x19x14 cm',
        'Vedação Metálica em Painel 2m',
        'Cabo de Aço Galvanizado 10mm',
        'Parafuso de Ancoragem M16x150',
    ];

    public function definition(): array
    {
        return [
            // unit_id must be provided via state() or seeder
            'name' => fake()->unique()->randomElement(self::POOL),
            'stock_quantity' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
