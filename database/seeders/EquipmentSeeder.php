<?php

namespace Database\Seeders;

use App\Core\Enums\EquipmentStatus;
use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EquipmentSeeder extends Seeder
{
    /**
     * Seed equipment covering all EquipmentStatus enum values.
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
            ['name' => 'Escavadora CAT 320D',               'serial_number' => 'CAT-320D-001',  'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => false, 'revision_interval' => 365, 'description' => 'Escavadora pesada para obras de grande escala', 'cost_per_hour' => 85.00],
            ['name' => 'Compressor de Ar Atlas Copco 250',  'serial_number' => 'ATLAS-250-002', 'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => true,  'revision_interval' => 180, 'description' => 'Compressor portátil para trabalhos de pneumática', 'cost_per_hour' => 25.00],
            ['name' => 'Martelo Pneumático Bosch',          'serial_number' => 'BOSCH-PH-003',  'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => true,  'revision_interval' => 90,  'description' => 'Martelo para demolição e rotura de pavimento', 'cost_per_hour' => 15.00],
            ['name' => 'Bomba de Água Diesel',              'serial_number' => 'DIESEL-PUMP-004','status' => EquipmentStatus::ACTIVE->value,      'is_loanable' => true,  'revision_interval' => 180, 'description' => 'Bomba para drenagem de obras', 'cost_per_hour' => 30.00],
            ['name' => 'Gerador 250 kVA Caterpillar',       'serial_number' => 'CAT-GEN-005',   'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => false, 'revision_interval' => 365, 'description' => 'Gerador industrial para alimentação de canteiros', 'cost_per_hour' => 95.00],
            ['name' => 'Vibrador de Placas Dinamarca',      'serial_number' => 'DIN-PLATE-007', 'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => true,  'revision_interval' => 120, 'description' => 'Compactador de solo e asfalto', 'cost_per_hour' => 20.00],
            ['name' => 'Broca Rotativa SPT',                'serial_number' => 'SPT-DRILL-008',  'status' => EquipmentStatus::ACTIVE->value,      'is_loanable' => false, 'revision_interval' => 730, 'description' => 'Sonda rotativa para sondagens geotécnicas', 'cost_per_hour' => 75.00],
            ['name' => 'Betoneira 350L',                    'serial_number' => 'MIXER-350-009',  'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => true,  'revision_interval' => 180, 'description' => 'Betoneira portátil para pequenas obras', 'cost_per_hour' => 22.00],
            ['name' => 'Grua Telescópica 25T',              'serial_number' => 'CRANE-25T-010',  'status' => EquipmentStatus::ACTIVE->value,       'is_loanable' => false, 'revision_interval' => 365, 'description' => 'Grua para movimentação de cargas até 25 toneladas', 'cost_per_hour' => 120.00],
            // ── In Use (on loan) ──
            ['name' => 'Martelo Demolidor Makita',          'serial_number' => 'MAK-DEM-013',    'status' => EquipmentStatus::IN_USE->value,       'is_loanable' => true,  'revision_interval' => 90,  'description' => 'Martelo demolidor emprestado a obra externa', 'cost_per_hour' => 18.00],
            // ── Under Maintenance ──
            ['name' => 'Retroescavadora JCB 3CX',           'serial_number' => 'JCB-3CX-006',    'status' => EquipmentStatus::UNDER_MAINTENANCE->value, 'is_loanable' => false, 'revision_interval' => 365, 'description' => 'Máquina versátil para escavação e carga — em manutenção', 'cost_per_hour' => 65.00],
            // ── Maintenance Pending ──
            ['name' => 'Cortador de Asfalto Husqvarna',     'serial_number' => 'HUS-CORT-014',   'status' => EquipmentStatus::MAINTENANCE_PENDING->value, 'is_loanable' => false, 'revision_interval' => 120, 'description' => 'Aguardando revisão programada', 'cost_per_hour' => 35.00],
            // ── Broken ──
            ['name' => 'Perfurador Pneumático Atlas',       'serial_number' => 'ATL-PERF-015',   'status' => EquipmentStatus::BROKEN->value,       'is_loanable' => false, 'revision_interval' => 90,  'description' => 'Avariado — necessita reparação urgente', 'cost_per_hour' => 15.00],
            // ── Under Repair ──
            ['name' => 'Serrador Circular Stihl',           'serial_number' => 'STI-SER-016',    'status' => EquipmentStatus::UNDER_REPAIR->value,  'is_loanable' => false, 'revision_interval' => 180, 'description' => 'Em reparação na oficina externa', 'cost_per_hour' => 12.00],
            // ── Reserved ──
            ['name' => 'Camião de Carga MAN TGS 18.480',   'serial_number' => 'MAN-TGS-011',    'status' => EquipmentStatus::RESERVED->value,     'is_loanable' => false, 'revision_interval' => 365, 'description' => 'Camião de carga reservado para obra na Zona Industrial', 'cost_per_hour' => 45.00],
            // ── Inactive ──
            ['name' => 'Gerador Antigo 100 kVA',           'serial_number' => 'OLD-GEN-012',    'status' => EquipmentStatus::INACTIVE->value,     'is_loanable' => false, 'revision_interval' => 0,   'description' => 'Gerador desativado — aguarda abate', 'cost_per_hour' => 0.00],
            // ── Retired ──
            ['name' => 'Compressor de Ar Old Ingersoll',    'serial_number' => 'ING-COMP-017',   'status' => EquipmentStatus::RETIRED->value,      'is_loanable' => false, 'revision_interval' => 0,   'description' => 'Equipamento abatido — fora de serviço', 'cost_per_hour' => 0.00],
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
                        ? (clone $lastRevision)->addDays($data['revision_interval'])
                        : null,
                ])
            );
        }

        $this->command->info('✅ Equipment seeded: ' . count($equipmentData) . ' items covering all statuses');
    }
}
