<?php

namespace Database\Seeders;

use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\User;
use App\Shared\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure equipment_manager role exists
        $equipmentManagerRole = Role::firstOrCreate(
            ['name' => 'equipment_manager'],
            ['id' => Str::uuid()]
        );

        // Get or create equipment managers (users with equipment_manager role)
        $equipmentManagers = [
            [
                'first_name' => 'Pedro',
                'last_name' => 'Equipamentos',
                'phone' => '+351912345700',
                'email' => 'pedro.equipamentos@cm.pt',
            ],
            [
                'first_name' => 'Rosa',
                'last_name' => 'Máquinas',
                'phone' => '+351912345701',
                'email' => 'rosa.maquinas@cm.pt',
            ],
        ];

        $managers = [];
        foreach ($equipmentManagers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'id' => Str::uuid(),
                    'password' => bcrypt('password123'),
                    'status' => 'active',
                ])
            );

            // Attach equipment_manager role if not already attached
            if (!$user->roles()->where('name', 'equipment_manager')->exists()) {
                $user->roles()->attach($equipmentManagerRole->id);
            }

            $managers[] = $user;
        }

        // Equipment data
        $equipmentData = [
            [
                'name' => 'Escavadora CAT 320D',
                'serial_number' => 'CAT-320D-001',
                'status' => 'active',
                'is_loanable' => false,
                'revision_interval_days' => 365,
                'description' => 'Escavadora pesada para obras de grande escala',
            ],
            [
                'name' => 'Compressor de Ar Atlas Copco 250',
                'serial_number' => 'ATLAS-250-002',
                'status' => 'active',
                'is_loanable' => true,
                'revision_interval_days' => 180,
                'description' => 'Compressor portátil para trabalhos de pneumática',
            ],
            [
                'name' => 'Martelo Pneumático Bosch',
                'serial_number' => 'BOSCH-PH-003',
                'status' => 'active',
                'is_loanable' => true,
                'revision_interval_days' => 90,
                'description' => 'Martelo para demolição e rotura de pavimento',
            ],
            [
                'name' => 'Bomba de Água Diesel Diesel',
                'serial_number' => 'DIESEL-PUMP-004',
                'status' => 'active',
                'is_loanable' => true,
                'revision_interval_days' => 180,
                'description' => 'Bomba para drenagem de obras',
            ],
            [
                'name' => 'Gerador 250 kVA Caterpillar',
                'serial_number' => 'CAT-GEN-005',
                'status' => 'active',
                'is_loanable' => false,
                'revision_interval_days' => 365,
                'description' => 'Gerador industrial para alimentação de canteiros',
            ],
            [
                'name' => 'Retroescavadora JCB 3CX',
                'serial_number' => 'JCB-3CX-006',
                'status' => 'maintenance',
                'is_loanable' => false,
                'revision_interval_days' => 365,
                'description' => 'Máquina versátil para escavação e carga',
            ],
            [
                'name' => 'Vibrador de Placas Dinamarca',
                'serial_number' => 'DIN-PLATE-007',
                'status' => 'active',
                'is_loanable' => true,
                'revision_interval_days' => 120,
                'description' => 'Compactador de solo e asfalto',
            ],
            [
                'name' => 'Broca Rotativa SPT',
                'serial_number' => 'SPT-DRILL-008',
                'status' => 'active',
                'is_loanable' => false,
                'revision_interval_days' => 730,
                'description' => 'Sonda rotativa para sondagens geotécnicas',
            ],
            [
                'name' => 'Betoneira 350L',
                'serial_number' => 'MIXER-350-009',
                'status' => 'active',
                'is_loanable' => true,
                'revision_interval_days' => 180,
                'description' => 'Betoneira portátil para pequenas obras',
            ],
            [
                'name' => 'Grua Telescópica 25T',
                'serial_number' => 'CRANE-25T-010',
                'status' => 'active',
                'is_loanable' => false,
                'revision_interval_days' => 365,
                'description' => 'Grua para movimentação de cargas até 25 toneladas',
            ],
        ];

        // Create equipment, cycling through managers
        foreach ($equipmentData as $index => $data) {
            $manager = $managers[$index % count($managers)];
            $lastRevision = now()->subMonths(rand(1, 6));

            Equipment::firstOrCreate(
                ['serial_number' => $data['serial_number']],
                array_merge($data, [
                    'id' => Str::uuid(),
                    'manager_id' => $manager->id,
                    'last_revision_date' => $lastRevision,
                    'next_revision_date' => $lastRevision->addDays($data['revision_interval_days']),
                ])
            );
        }

        $this->command->info('✅ Equipment seeded successfully with ' . count($equipmentData) . ' items');
    }
}
