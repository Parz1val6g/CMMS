<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\EquipmentType;
use Illuminate\Database\Seeder;

class EquipmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Veículo',         'category' => 'vehicle', 'active' => true],
            ['name' => 'Gerador',         'category' => 'general', 'active' => true],
            ['name' => 'Compressor',      'category' => 'general', 'active' => true],
            ['name' => 'Betoneira',       'category' => 'general', 'active' => true],
            ['name' => 'Andaime',         'category' => 'general', 'active' => true],
            ['name' => 'Martelo Pneumático', 'category' => 'general', 'active' => true],
            ['name' => 'Bomba de Água',   'category' => 'general', 'active' => true],
            ['name' => 'Vibrador de Placas', 'category' => 'general', 'active' => true],
            ['name' => 'Grua',            'category' => 'vehicle', 'active' => true],
            ['name' => 'Escavadora',      'category' => 'vehicle', 'active' => true],
            ['name' => 'Retroescavadora', 'category' => 'vehicle', 'active' => true],
            ['name' => 'Camião',               'category' => 'vehicle', 'active' => true],
            ['name' => 'Cortadora de Asfalto',  'category' => 'general', 'active' => true],
            ['name' => 'Perfuradora',           'category' => 'general', 'active' => true],
            ['name' => 'Serra Circular',        'category' => 'general', 'active' => true],
        ];

        foreach ($types as $type) {
            EquipmentType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        $this->command->info('✅ EquipmentType seeded: ' . count($types) . ' types');
    }
}
