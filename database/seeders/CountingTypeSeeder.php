<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\CountingType;
use Illuminate\Database\Seeder;

class CountingTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Deactivate old incorrect types
        CountingType::whereIn('value', ['unit', 'weight', 'length', 'area', 'volume'])
            ->update(['active' => false]);

        $types = [
            ['name' => 'Quilómetros', 'value' => 'km',      'active' => true],
            ['name' => 'Horas',       'value' => 'hours',   'active' => true],
            ['name' => 'Metros',      'value' => 'meters',  'active' => true],
            ['name' => 'Dias',        'value' => 'days',    'active' => true],
            ['name' => 'Semanas',     'value' => 'weeks',   'active' => true],
            ['name' => 'Meses',       'value' => 'months',  'active' => true],
            ['name' => 'Anos',        'value' => 'years',   'active' => true],
        ];

        foreach ($types as $type) {
            CountingType::updateOrCreate(
                ['value' => $type['value']],
                $type
            );
        }

        $this->command->info('✅ CountingType seeded: ' . count($types) . ' types');
    }
}
