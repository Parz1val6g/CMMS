<?php

namespace Database\Seeders;

use App\Core\Enums\ServiceOrderStatus as SOStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\WorkflowType;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Equipments\Models\Equipment;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    /**
     * Exhaustive state coverage:
     * - Every ServiceOrderStatus (4)
     * - Every Priority (4)
     * - Every WorkflowType (2)
     * - Cross-reference: Client-created vs Admin-managed where logical
     */
    public function run(): void
    {
        $clients      = Client::all();
        $admin        = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $manager      = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
        $locations    = Location::all();
        $serviceTypes = ServiceType::all();
        $loanableEq   = Equipment::where('is_loanable', true)->first();

        if (!$admin || !$manager || $clients->isEmpty() || $locations->isEmpty() || $serviceTypes->isEmpty()) {
            return;
        }

        $now = now();
        $twoMonthsAgo = (clone $now)->modify('-60 days');

        /**
         * Each entry: [status, priority, workflow_type, manager, description, service_type_hint, equipment_id]
         * This covers ALL enum values deterministically.
         */
        $orders = [
            // ── Regular workflow, all statuses × all priorities ──
            ['status' => SOStatus::PENDING,     'priority' => Priority::LOW,    'wf' => WorkflowType::STANDARD, 'manager' => $manager, 'st' => 'Aprovação de Projetos',   'desc' => 'Análise de projeto de construção civil para edifício habitacional de 4 pisos'],
            ['status' => SOStatus::PENDING,     'priority' => Priority::NORMAL, 'wf' => WorkflowType::STANDARD, 'manager' => $manager, 'st' => 'Emissão de Licenças',     'desc' => 'Processamento de licença comercial para novo estabelecimento na Rua Direita'],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::HIGH,   'wf' => WorkflowType::STANDARD, 'manager' => $admin,   'st' => 'Iluminação Pública',     'desc' => 'Substituição de luminárias fundidas na Av. Principal por LED de baixo consumo'],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::URGENT, 'wf' => WorkflowType::STANDARD, 'manager' => $admin,   'st' => 'Abastecimento de Água',  'desc' => 'Reparação de rotura na conduta principal de água com escavação e soldadura urgente'],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::NORMAL, 'wf' => WorkflowType::STANDARD, 'manager' => $manager, 'st' => 'Pavimentação',            'desc' => 'Reparação de piso danificado na Rua Nova com aplicação de nova camada de asfalto'],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::HIGH,   'wf' => WorkflowType::STANDARD, 'manager' => $admin,   'st' => 'Sinalização de Trânsito', 'desc' => 'Pintura de passadeiras na Av. Central com tinta termoplástica — concluída'],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::LOW,    'wf' => WorkflowType::STANDARD, 'manager' => $manager, 'st' => 'Vistoria de Imóveis',     'desc' => 'Vistoria para licença de habitação cancelada por falta de documentação'],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::NORMAL, 'wf' => WorkflowType::STANDARD, 'manager' => $admin,   'st' => 'Limpeza Urbana',          'desc' => 'Limpeza extraordinária cancelada devido a condições meteorológicas adversas'],
            // ── Loan workflow ──
            ['status' => SOStatus::PENDING,     'priority' => Priority::NORMAL, 'wf' => WorkflowType::LOAN,     'manager' => $manager, 'st' => null, 'desc' => 'Pedido de empréstimo de compressor Atlas Copco para obra na Zona Industrial', 'eq' => $loanableEq->id],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::HIGH,   'wf' => WorkflowType::LOAN,     'manager' => $admin,   'st' => null, 'desc' => 'Empréstimo de martelo pneumático Bosch para demolição em obra municipal', 'eq' => $loanableEq->id],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::NORMAL, 'wf' => WorkflowType::LOAN,     'manager' => $manager, 'st' => null, 'desc' => 'Devolução de bomba de água Diesel após período de empréstimo de 15 dias', 'eq' => $loanableEq->id],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::LOW,    'wf' => WorkflowType::LOAN,     'manager' => $admin,   'st' => null, 'desc' => 'Pedido de empréstimo cancelado por desistência do requerente', 'eq' => $loanableEq->id],
            // ── Edge cases: urgent + pending auto-promotes to in_progress, so we add low+in_progress ──
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::LOW,    'wf' => WorkflowType::STANDARD, 'manager' => $manager, 'st' => 'Manutenção de Jardins',  'desc' => 'Manutenção de canteiros na Praça Municipal com corte de ervas e fertilização'],
            ['status' => SOStatus::PENDING,     'priority' => Priority::URGENT, 'wf' => WorkflowType::STANDARD, 'manager' => $admin,   'st' => 'Saneamento',             'desc' => 'Desobstrução urgente de coletor entupido na Rua do Mercado'],
        ];

        $client = $clients->first();
        $counter = 0;

        foreach ($orders as $def) {
            $counter++;
            $year = $now->format('Y');
            $process = 'OS/' . $year . '/GALLERY-' . str_pad((string)$counter, 3, '0', STR_PAD_LEFT);

            $createdAt = (clone $twoMonthsAgo)->modify('+' . (($counter - 1) * 4) . ' days');
            $executionDate = match ($def['status']) {
                SOStatus::COMPLETED   => (clone $createdAt)->modify('+15 days'),
                SOStatus::IN_PROGRESS => (clone $createdAt)->modify('+2 days'),
                SOStatus::PENDING     => (clone $createdAt)->modify('+30 days'),
                SOStatus::CANCELLED   => null,
            };
            // Completed: use a past date for execution
            if ($def['status'] === SOStatus::COMPLETED) {
                $createdAt = (clone $twoMonthsAgo)->modify('+' . (($counter - 1) * 4) . ' days');
                $executionDate = (clone $createdAt)->modify('+5 days');
            }

            $serviceType = $def['st'] ? $serviceTypes->firstWhere('name', $def['st']) : null;

            ServiceOrder::create([
                'process'         => $process,
                'client_id'       => $client->id,
                'manager_id'      => $def['manager']->id,
                'location_id'     => $locations->random()->id,
                'service_type_id' => $serviceType?->id,
                'workflow_type'   => $def['wf']->value,
                'equipment_id'    => $def['eq'] ?? null,
                'priority'        => $def['priority']->value,
                'execution_date'  => $executionDate,
                'status'          => $def['status']->value,
                'description'     => $def['desc'],
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);
        }
    }
}
