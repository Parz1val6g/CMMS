<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        // Get districts mapping
        $districts = DB::table('districts')->pluck('id', 'name');

        // Read JSON file
        $jsonPath = database_path('dados_portugal.json');
        $data = json_decode(file_get_contents($jsonPath), true);

        $count = 0;
        foreach ($data as $municipality) {
            $districtName = $municipality['distrito'];

            if (isset($districts[$districtName])) {
                DB::table('municipalities')->insert([
                    'id' => Str::uuid(),
                    'district_id' => $districts[$districtName],
                    'name' => $municipality['nome'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        echo "✓ Inserted " . $count . " municipalities\n";
    }
}
