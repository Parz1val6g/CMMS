<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Quilograma', 'abbreviation' => 'kg'],
            ['name' => 'Metro', 'abbreviation' => 'm'],
            ['name' => 'Metro Linear', 'abbreviation' => 'ml'],
            ['name' => 'Metro Quadrado', 'abbreviation' => 'm²'],
            ['name' => 'Metro Cúbico', 'abbreviation' => 'm³'],
            ['name' => 'Litro', 'abbreviation' => 'l'],
            ['name' => 'Unidade', 'abbreviation' => 'un'],
            ['name' => 'Balde', 'abbreviation' => 'bld'],
            ['name' => 'Saco', 'abbreviation' => 'sco'],
            ['name' => 'Caixa', 'abbreviation' => 'cx'],
            ['name' => 'Rolo', 'abbreviation' => 'rol'],
            ['name' => 'Placa', 'abbreviation' => 'plc'],
            ['name' => 'Hora', 'abbreviation' => 'h'],
            ['name' => 'Dia', 'abbreviation' => 'd'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert([
                'id' => Str::uuid(),
                'name' => $unit['name'],
                'abbreviation' => $unit['abbreviation'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
