<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $units = DB::table('units')->pluck('id', 'name');

        $materials = [
            ['name' => 'Cimento Portland CEM II 32,5N',   'unit' => 'Quilograma',    'stock' => 800],
            ['name' => 'Areia de Rio Lavada',              'unit' => 'Metro Cúbico',   'stock' => 60],
            ['name' => 'Brita Calcária 12/25 mm',          'unit' => 'Metro Cúbico',   'stock' => 80],
            ['name' => 'Betão Betuminoso a Quente AC14',   'unit' => 'Quilograma',    'stock' => 1500],
            ['name' => 'Calçada de Granito 11x11 cm',      'unit' => 'Metro Quadrado', 'stock' => 250],
            ['name' => 'Tubo de PVC Corrugado DN200',      'unit' => 'Metro Linear',   'stock' => 300],
            ['name' => 'Tubo de PEAD DN90 PN10',           'unit' => 'Metro Linear',   'stock' => 200],
            ['name' => 'Condutor Elétrico Cu 4mm²',        'unit' => 'Metro Linear',   'stock' => 500],
            ['name' => 'Luminária LED 150W IP66',          'unit' => 'Unidade',        'stock' => 30],
            ['name' => 'Sinal de Trânsito Vertical 60cm',  'unit' => 'Unidade',        'stock' => 20],
            ['name' => 'Tinta de Sinalização Branca 25kg', 'unit' => 'Quilograma',    'stock' => 100],
            ['name' => 'Barreira de Segurança Plástica',   'unit' => 'Unidade',        'stock' => 50],
            ['name' => 'Geotêxtil Não-Tecido 200g/m²',     'unit' => 'Metro Quadrado', 'stock' => 500],
            ['name' => 'Argamassa de Reparação Estrutural', 'unit' => 'Quilograma',    'stock' => 60],
            ['name' => 'Lajeta de Betão 40x40x5 cm',       'unit' => 'Metro Quadrado', 'stock' => 200],
        ];

        foreach ($materials as $mat) {
            $unitId = $units[$mat['unit']] ?? null;
            if (!$unitId) continue;

            DB::table('materials')->insert([
                'id'         => Str::uuid(),
                'name'       => $mat['name'],
                'unit_id'    => $unitId,
                'stock_quantity' => $mat['stock'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
