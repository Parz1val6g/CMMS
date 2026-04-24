<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'Aveiro',
            'Beja',
            'Braga',
            'Bragança',
            'Castelo Branco',
            'Covilhã',
            'Covilhã',
            'Faro',
            'Guarda',
            'Leiria',
            'Lisboa',
            'Madeira',
            'Portalegre',
            'Porto',
            'Santarém',
            'Setúbal',
            'Viana do Castelo',
            'Vila Real',
            'Viseu',
            'Açores',
        ];

        foreach ($districts as $district) {
            DB::table('districts')->insert([
                'id' => Str::uuid(),
                'name' => $district,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
