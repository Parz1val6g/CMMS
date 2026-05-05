<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EquipmentSeeder extends Seeder
{
    /**
     * Seed equipment covering all possible statuses:
     * active, maintenance, reserved, inactive
     */
    public function run(): void
    {
        // Equipment manager user (created by UserSeeder)
        $equipmentManager = User::whereHas('roles', fn($q) => $q->where('name', 'equipment_manager'))->first();

        if (!$equipmentManager) {
            $this->command->error('❌ equipment_manager user not found. Run UserSeeder first.');
            return;
        }

        $equipmentData = [
            // ── Active equipment ──
            ['name' => 'Escavadora CAT 320D',               'serial_number' => 'CAT-320D-001',  'status' => 'active',      'is_loanable' => false, 'revision_interval_days' => 365, 'description' => 'Escavadora pesada para obras de grande escala'],
            ['name' => 'Compressor de Ar Atlas Copco 250',  'serial_number' => 'ATLAS-250-002', 'status' => 'active',      'is_loanable' => true,  'revision_interval_days' => 180, 'description' => 'Compressor portátil para trabalhos de pneumática'],
            ['name' => 'Martelo Pneumático Bosch',          'serial_number' => 'BOSCH-PH-003',  'status' => 'active',      'is_loanable' => true,  'revision_interval_days' => 90,  'description' => 'Martelo para demolição e rotura de pavimento'],
            ['name' => 'Bomba de Água Diesel',              'serial_number' => 'DIESEL-PUMP-004','status' => 'active',     'is_loanable' => true,  'revision_interval_days' => 180, 'description' => 'Bomba para drenagem de obras'],
            ['name' => 'Gerador 250 kVA Caterpillar',       'serial_number' => 'CAT-GEN-005',   'status' => 'active',      'is_loanable' => false, 'revision_interval_days' => 365, 'description' => 'Gerador industrial para alimentação de canteiros'],
            ['name' => 'Vibrador de Placas Dinamarca',      'serial_number' => 'DIN-PLATE-007', 'status' => 'active',      'is_loanable' => true,  'revision_interval_days' => 120, 'description' => 'Compactador de solo e asfalto'],
            ['name' => 'Broca Rotativa SPT',                'serial_number' => 'SPT-DRILL-008',  'status' => 'active',     'is_loanable' => false, 'revision_interval_days' => 730, 'description' => 'Sonda rotativa para sondagens geotécnicas'],
            ['name' => 'Betoneira 350L',                    'serial_number' => 'MIXER-350-009',  'status' => 'active',      'is_loanable' => true,  'revision_interval_days' => 180, 'description' => 'Betoneira portátil para pequenas obras'],
            ['name' => 'Grua Telescópica 25T',              'serial_number' => 'CRANE-25T-010',  'status' => 'active',      'is_loanable' => false, 'revision_interval_days' => 365, 'description' => 'Grua para movimentação de cargas até 25 toneladas'],
            // ── Maintenance ──
            ['name' => 'Retroescavadora JCB 3CX',           'serial_number' => 'JCB-3CX-006',   'status' => 'maintenance', 'is_loanable' => false, 'revision_interval_days' => 365, 'description' => 'Máquina versátil para escavação e carga — em manutenção'],
            // ── Reserved ──
            ['name' => 'Camião de Carga MAN TGS 18.480',   'serial_number' => 'MAN-TGS-011',    'status' => 'reserved',    'is_loanable' => false, 'revision_interval_days' => 365, 'description' => 'Camião de carga reservado para obra na Zona Industrial'],
            // ── Inactive ──
            ['name' => 'Gerador Antigo 100 kVA',           'serial_number' => 'OLD-GEN-012',    'status' => 'inactive',    'is_loanable' => false, 'revision_interval_days' => 0,   'description' => 'Gerador desativado — aguarda abate'],
        ];

        foreach ($equipmentData as $index => $data) {
            $lastRevision = $data['revision_interval_days'] > 0
                ? now()->subMonths(rand(1, 6))
                : null;

            Equipment::firstOrCreate(
                ['serial_number' => $data['serial_number']],
                array_merge($data, [
                    'id'                  => Str::uuid(),
                    'manager_id'          => $equipmentManager->id,
                    'last_revision_date'  => $lastRevision,
                    'next_revision_date'  => $lastRevision
                        ? (clone $lastRevision)->addDays($data['revision_interval_days'])
                        : null,
                ])
            );
        }

        $this->command->info('✅ Equipment seeded: ' . count($equipmentData) . ' items covering all statuses');
    }
}
