<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $lisboaDistrict = DB::table('districts')->where('name', 'Lisboa')->first();

        $municipalities = [
            'Alcântara',
            'Almada',
            'Amadora',
            'Aveiro',
            'Barreiro',
            'Caparica',
            'Cascais',
            'Lisboa',
            'Loures',
            'Mafra',
            'Odivelas',
            'Oeiras',
            'Sesimbra',
            'Setúbal',
            'Sintra',
            'Sobral Monte Agraço',
            'Tavira',
            'Tejo',
            'Tortosendo',
            'Torres Vedras',
        ];

        foreach ($municipalities as $municipality) {
            DB::table('municipalities')->insert([
                'id' => Str::uuid(),
                'district_id' => $lisboaDistrict->id,
                'name' => $municipality,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
