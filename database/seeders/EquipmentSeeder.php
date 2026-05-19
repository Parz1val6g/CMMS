<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\Equipment;
use App\Core\Enums\EquipmentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $types = DB::table('equipment_types')->pluck('id', 'name');
        $counting = DB::table('counting_types')->pluck('id', 'name');
        $equipManagerId = DB::table('users')->where('email', 'pedro.carvalho@cm-mangualde.pt')->value('id');

        $items = [
            // ── Vehicle Fleet ──
            ['type' => 'Camião', 'name' => 'Camião de Carga MAN TGS 18.480',        'serial' => 'MAN-TGS18-480-2019',  'status' => EquipmentStatus::ACTIVE,           'loanable' => false, 'cost' => 45.00,  'revision' => 365, 'internal' => 'VEI-001', 'year' => 2019],
            ['type' => 'Camião', 'name' => 'Camião Basculante Volvo FMX 500',       'serial' => 'VOL-FMX500-2021-01',   'status' => EquipmentStatus::ACTIVE,           'loanable' => false, 'cost' => 55.00,  'revision' => 365, 'internal' => 'VEI-002', 'year' => 2021],
            ['type' => 'Escavadora', 'name' => 'Escavadora Caterpillar 320D',       'serial' => 'CAT-320D-Z9B00521',    'status' => EquipmentStatus::ACTIVE,           'loanable' => false, 'cost' => 85.00,  'revision' => 365, 'internal' => 'VEI-003', 'year' => 2018],
            ['type' => 'Escavadora', 'name' => 'Mini-Escavadora Bobcat E35',        'serial' => 'BOB-E35-B4N710422',    'status' => EquipmentStatus::ACTIVE,           'loanable' => false, 'cost' => 65.00,  'revision' => 365, 'internal' => 'VEI-004', 'year' => 2020],
            ['type' => 'Retroescavadora', 'name' => 'Retroescavadora JCB 3CX',        'serial' => 'JCB-3CX-LF2019342',   'status' => EquipmentStatus::UNDER_MAINTENANCE, 'loanable' => false, 'cost' => 65.00,  'revision' => 365, 'internal' => 'VEI-005', 'year' => 2017],
            ['type' => 'Grua', 'name' => 'Grua Telescópica Liebherr LTM 1050',      'serial' => 'LIE-LTM1050-078912',   'status' => EquipmentStatus::ACTIVE,           'loanable' => false, 'cost' => 120.00, 'revision' => 365, 'internal' => 'VEI-006', 'year' => 2016],

            // ── Compressors & Pneumatic Tools ──
            ['type' => 'Compressor', 'name' => 'Compressor de Ar Atlas Copco XAS 185',  'serial' => 'ATL-XAS185-APF910234', 'status' => EquipmentStatus::IN_USE,      'loanable' => true,  'cost' => 25.00,  'revision' => 180, 'internal' => 'EQP-001', 'year' => 2020],
            ['type' => 'Martelo Pneumático', 'name' => 'Martelo Pneumático Bosch GSH 16',  'serial' => 'BOS-GSH16-3456789',     'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 15.00,  'revision' => 90,  'internal' => 'EQP-002', 'year' => 2019],
            ['type' => 'Martelo Pneumático', 'name' => 'Martelo Demolidor Makita HM1812',  'serial' => 'MAK-HM1812-8923456',     'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 18.00,  'revision' => 90,  'internal' => 'EQP-003', 'year' => 2021],

            // ── Water Pumps ──
            ['type' => 'Bomba de Água', 'name' => 'Bomba Submersível Grundfos SL1.50', 'serial' => 'GRU-SL150-99210683',       'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 30.00,  'revision' => 180, 'internal' => 'EQP-004', 'year' => 2020],
            ['type' => 'Bomba de Água', 'name' => 'Motobomba Diesel Honda WT40X',     'serial' => 'HON-WT40X-DK510281',       'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 22.00,  'revision' => 180, 'internal' => 'EQP-005', 'year' => 2019],

            // ── Generators ──
            ['type' => 'Gerador', 'name' => 'Gerador Diesel Caterpillar DE250GC',      'serial' => 'CAT-DE250GC-CAT001234',   'status' => EquipmentStatus::ACTIVE,        'loanable' => false, 'cost' => 95.00,  'revision' => 365, 'internal' => 'EQP-006', 'year' => 2018],
            ['type' => 'Gerador', 'name' => 'Gerador Portátil Honda EU70iS',           'serial' => 'HON-EU70IS-AS849201',      'status' => EquipmentStatus::INACTIVE,      'loanable' => true,  'cost' => 12.00,  'revision' => 180, 'internal' => 'EQP-007', 'year' => 2023],

            // ── Concrete & Compaction ──
            ['type' => 'Betoneira', 'name' => 'Betoneira Elétrica IMER 350L',            'serial' => 'IME-350L-BT340912',      'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 22.00,  'revision' => 180, 'internal' => 'EQP-008', 'year' => 2021],
            ['type' => 'Vibrador de Placas', 'name' => 'Placa Vibratória Bomag BPR 70/70', 'serial' => 'BOM-BPR7070-00192834',    'status' => EquipmentStatus::ACTIVE,        'loanable' => true,  'cost' => 20.00,  'revision' => 120, 'internal' => 'EQP-009', 'year' => 2020],

            // ── Cutting & Drilling ──
            ['type' => 'Cortadora de Asfalto', 'name' => 'Cortadora de Asfalto Husqvarna FS 400', 'serial' => 'HUS-FS400-2021000912',     'status' => EquipmentStatus::MAINTENANCE_PENDING, 'loanable' => false, 'cost' => 35.00,  'revision' => 120, 'internal' => 'EQP-010', 'year' => 2021],
            ['type' => 'Perfuradora', 'name' => 'Perfuradora de Rocha Atlas Copco RD20', 'serial' => 'ATL-RD20-APC102938',       'status' => EquipmentStatus::BROKEN,        'loanable' => false, 'cost' => 15.00,  'revision' => 90,  'internal' => 'EQP-011', 'year' => 2018],

            // ── Saws ──
            ['type' => 'Serra Circular', 'name' => 'Serra Circular Stihl TS 420',          'serial' => 'STI-TS420-519284763',     'status' => EquipmentStatus::UNDER_REPAIR,  'loanable' => false, 'cost' => 12.00,  'revision' => 180, 'internal' => 'EQP-012', 'year' => 2019],

            // ── Retired / Old ──
            ['type' => 'Compressor', 'name' => 'Compressor Ingersoll Rand P185 (Abatido)', 'serial' => 'ING-P185-IR772134',       'status' => EquipmentStatus::RETIRED,       'loanable' => false, 'cost' => 0.00,   'revision' => 0,   'internal' => 'EQP-013', 'year' => 2008],
        ];

        foreach ($items as $i) {
            $typeId = $types[$i['type']] ?? null;
            if (!$typeId) {
                echo "WARNING: Equipment type '{$i['type']}' not found — skipping.\n";
                continue;
            }

            Equipment::firstOrCreate(
                ['serial_number' => $i['serial']],
                [
                    'name'                => $i['name'],
                    'internal_reference'  => $i['internal'],
                    'equipment_type_id'   => $typeId,
                    'manufacturing_year'  => $i['year'],
                    'counting_type_id'    => $counting['Horas'] ?? null,
                    'revision_interval'   => $i['revision'],
                    'status'              => $i['status']->value,
                    'is_loanable'         => $i['loanable'],
                    'cost_per_hour'       => $i['cost'],
                    'manager_id'          => $equipManagerId,
                ]
            );
        }

        echo "Equipamento semeado: " . count($items) . " equipamentos\n";
    }
}
