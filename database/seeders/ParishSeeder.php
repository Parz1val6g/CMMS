<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ParishSeeder extends Seeder
{
    public function run(): void
    {
        // Get all municipalities mapping
        $municipalities = DB::table('municipalities')->pluck('id', 'name');

        // Read JSON file
        $jsonPath = database_path('dados_portugal.json');
        $data = json_decode(file_get_contents($jsonPath), true);

        $count = 0;
        foreach ($data as $municipality) {
            $municipalityName = $municipality['nome'];

            if (isset($municipalities[$municipalityName]) && isset($municipality['freguesias'])) {
                foreach ($municipality['freguesias'] as $parish) {
                    DB::table('parishes')->insert([
                        'id' => Str::uuid(),
                        'municipality_id' => $municipalities[$municipalityName],
                        'name' => $parish['nome'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
            }
        }

        echo "✓ Inserted " . $count . " parishes\n";
    }
}
