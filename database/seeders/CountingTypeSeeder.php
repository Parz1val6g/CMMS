<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\CountingType;
use Illuminate\Database\Seeder;

class CountingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Unitário',    'value' => 'unit',     'active' => true],
            ['name' => 'Peso',        'value' => 'weight',   'active' => true],
            ['name' => 'Comprimento', 'value' => 'length',   'active' => true],
            ['name' => 'Área',        'value' => 'area',     'active' => true],
            ['name' => 'Volume',      'value' => 'volume',   'active' => true],
        ];

        foreach ($types as $type) {
            CountingType::firstOrCreate(
                ['value' => $type['value']],
                $type
            );
        }

        $this->command->info('✅ CountingType seeded: ' . count($types) . ' types');
    }
}
