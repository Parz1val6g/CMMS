<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        // Read JSON file and extract unique districts
        $jsonPath = database_path('dados_portugal.json');
        $data = json_decode(file_get_contents($jsonPath), true);

        $districts = array_unique(array_column($data, 'distrito'));
        sort($districts);

        foreach ($districts as $district) {
            DB::table('districts')->insert([
                'id' => Str::uuid(),
                'name' => $district,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✓ Inserted " . count($districts) . " districts\n";
    }
}
