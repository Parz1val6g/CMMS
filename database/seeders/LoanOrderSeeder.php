<?php

namespace Database\Seeders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\LoanOrderStatus;
use App\Features\Entities\Models\Entity;
use App\Features\Equipments\Models\Equipment;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\Locations\Models\Location;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanOrderSeeder extends Seeder
{
    /**
     * Exhaustive LoanOrder coverage across the full lifecycle.
     *
     * Creates loan orders demonstrating every LoanOrderStatus, with
     * realistic equipment assignments, associated tasks, and temporal
     * consistency (approved_at < checked_out_at < returned_at).
     */
    public function run(): void
    {
        $entities   = Entity::all();
        $managers   = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();
        $locations  = Location::all();
        $parishes   = DB::table('parishes')->pluck('id');
        $equipments = Equipment::where('is_loanable', true)
                        ->where('status', EquipmentStatus::ACTIVE->value)
                        ->get();

        if ($entities->isEmpty() || $managers->isEmpty()) {
            $this->command->warn('LoanOrderSeeder: missing entities or managers — skipping.');
            return;
        }

        $now         = now();
        $twoWeeksAgo = (clone $now)->modify('-14 days');
        $oneWeekAgo  = (clone $now)->modify('-7 days');
        $threeDaysAgo = (clone $now)->modify('-3 days');

        // Assign specific entities to loans for meaningful scenarios
        $cmMangualde  = $entities->firstWhere('name', 'Câmara Municipal de Mangualde');
        $cmViseu      = $entities->firstWhere('name', 'Câmara Municipal de Viseu');
        $jfMangualde  = $entities->firstWhere('name', 'Junta de Freguesia de Mangualde');
        $jfAbrunhosa  = $entities->firstWhere('name', 'Junta de Freguesia de Abrunhosa-a-Velha');
        $bombeiros    = $entities->firstWhere('name', 'Associação Humanitária dos Bombeiros Voluntários de Mangualde');
        $scm          = $entities->firstWhere('name', 'Santa Casa da Misericórdia de Mangualde');

        $approvedBy = $managers->first();

        $orders = [
            // ── PENDING ──
            [
                'entity'       => $cmMangualde ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::PENDING,
                'description'  => 'Pedido de empréstimo de compressor e martelo pneumático para demolição de estrutura na Rua Direita.',
                'equip_idx'    => [0, 1],
                'needs_op'     => [false, false],
                'created_days' => -2,
            ],
            [
                'entity'       => $jfMangualde ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::PENDING,
                'description'  => 'Empréstimo de betoneira 350L para obras de requalificação do Largo da Igreja Matriz.',
                'equip_idx'    => [2],
                'needs_op'     => [false],
                'created_days' => -1,
            ],
            // ── APPROVED ──
            [
                'entity'       => $scm ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::APPROVED,
                'description'  => 'Empréstimo de gerador portátil para evento solidário na Praça do Município.',
                'equip_idx'    => [3],
                'needs_op'     => [false],
                'created_days' => -5,
                'approved'     => true,
            ],
            [
                'entity'       => $bombeiros ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::APPROVED,
                'description'  => 'Empréstimo de bomba de água submersível para operações de escoamento na zona baixa da cidade.',
                'equip_idx'    => [4],
                'needs_op'     => [true],
                'created_days' => -4,
                'approved'     => true,
            ],
            // ── CHECKED_OUT ──
            [
                'entity'       => $cmViseu ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::CHECKED_OUT,
                'description'  => 'Empréstimo de cortadora de asfalto Husqvarna FS 400 para trabalhos de repavimentação na Av. da Europa.',
                'equip_idx'    => [0],
                'needs_op'     => [true],
                'created_days' => -10,
                'approved'     => true,
                'checked_out'  => true,
            ],
            [
                'entity'       => $jfAbrunhosa ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::CHECKED_OUT,
                'description'  => 'Empréstimo de vibrador de placas Bomag para compactação de passeios na Rua Principal.',
                'equip_idx'    => [2],
                'needs_op'     => [false],
                'created_days' => -8,
                'approved'     => true,
                'checked_out'  => true,
            ],
            // ── RETURNED ──
            [
                'entity'       => $cmMangualde ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::RETURNED,
                'description'  => 'Empréstimo de compressor Atlas Copco XAS 185 para obra de saneamento na Zona Industrial. Devolvido dentro do prazo.',
                'equip_idx'    => [0, 1],
                'needs_op'     => [true, false],
                'created_days' => -30,
                'approved'     => true,
                'checked_out'  => true,
                'returned'     => true,
            ],
            [
                'entity'       => $bombeiros ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::RETURNED,
                'description'  => 'Empréstimo de motobomba diesel Honda WT40X para prevenção de incêndios florestais. Devolvida após época crítica.',
                'equip_idx'    => [4],
                'needs_op'     => [false],
                'created_days' => -60,
                'approved'     => true,
                'checked_out'  => true,
                'returned'     => true,
            ],
            // ── CANCELLED ──
            [
                'entity'       => $jfMangualde ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::CANCELLED,
                'description'  => 'Pedido de empréstimo de perfuradora cancelado por desistência da Junta de Freguesia.',
                'equip_idx'    => [5],
                'needs_op'     => [false],
                'created_days' => -15,
                'cancelled'    => true,
            ],
            [
                'entity'       => $cmViseu ?? $entities->first(),
                'manager'      => $managers->random(),
                'status'       => LoanOrderStatus::CANCELLED,
                'description'  => 'Pedido de empréstimo cancelado — o equipamento solicitado entrou em manutenção antes da aprovação.',
                'equip_idx'    => [0],
                'needs_op'     => [true],
                'created_days' => -12,
                'cancelled'    => true,
            ],
        ];

        $counter = 0;

        foreach ($orders as $def) {
            $counter++;
            $createdAt = (clone $now)->modify($def['created_days'] . ' days');

            $approvedAt  = ($def['approved'] ?? false)    ? (clone $createdAt)->modify('+1 day')  : null;
            $checkedOutAt = ($def['checked_out'] ?? false) ? (clone $createdAt)->modify('+2 days') : null;
            $returnedAt  = ($def['returned'] ?? false)     ? (clone $createdAt)->modify('+20 days') : null;
            $cancelledAt = ($def['cancelled'] ?? false)    ? (clone $createdAt)->modify('+3 days') : null;

            $loan = LoanOrder::create([
                'reference'           => 'EMP/' . $now->format('Y') . '/' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
                'entity_id'           => $def['entity']->id,
                'manager_id'          => $def['manager']->id,
                'location_id'         => $locations->isNotEmpty() ? $locations->random()->id : null,
                'delivery_location_id'=> $parishes->isNotEmpty() ? $parishes->random() : null,
                'status'              => $def['status']->value,
                'description'         => $def['description'],
                'approved_by'         => $approvedAt ? $approvedBy?->id : null,
                'approved_at'         => $approvedAt,
                'checked_out_at'      => $checkedOutAt,
                'returned_at'         => $returnedAt,
                'cancelled_at'        => $cancelledAt,
                'cancelled_by'        => $cancelledAt ? $def['manager']->id : null,
                'created_at'          => $createdAt,
                'updated_at'          => $createdAt,
            ]);

            // Attach equipment via pivot
            foreach ($def['equip_idx'] as $i => $eqIdx) {
                $equipment = $equipments->get($eqIdx);
                if (!$equipment) {
                    $equipment = $equipments->random();
                }
                $startDate = $checkedOutAt ? $checkedOutAt->toDateString() : (clone $createdAt)->modify('+2 days')->toDateString();
                $endDate   = $returnedAt ? $returnedAt->toDateString() : (clone $createdAt)->modify('+22 days')->toDateString();

                DB::table('equipment_loan_order')->insert([
                    'equipment_id'   => $equipment->id,
                    'loan_order_id'  => $loan->id,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'needs_operator' => $def['needs_op'][$i] ?? false,
                    'created_at'     => $createdAt,
                    'updated_at'     => $createdAt,
                ]);
            }

            // Criar tarefa de entrega para empréstimos em curso ou devolvidos
            // Os campos priority, start_date e end_date foram adicionados pelas migrações
            // 2026_06_02_100002 e 2026_05_29_000002 respetivamente — são nullable para tarefas de empréstimo.
            if ($checkedOutAt) {
                $checkoutStatus = $returnedAt ? 'completed' : 'in_progress';
                DB::table('tasks')->insert([
                    'id'            => Str::uuid(),
                    'taskable_type' => LoanOrder::class,
                    'taskable_id'   => $loan->id,
                    'manager_id'    => $def['manager']->id,
                    'description'   => 'Entrega e verificação de equipamentos em regime de empréstimo',
                    'status'        => $checkoutStatus,
                    'priority'      => 'normal',
                    'start_date'    => $checkedOutAt->toDateString(),
                    'end_date'      => $returnedAt ? $returnedAt->toDateString() : (clone $checkedOutAt)->modify('+30 days')->toDateString(),
                    'created_at'    => $checkedOutAt,
                    'updated_at'    => $checkedOutAt,
                ]);

                // Criar tarefa de devolução para empréstimos devolvidos
                if ($returnedAt) {
                    DB::table('tasks')->insert([
                        'id'            => Str::uuid(),
                        'taskable_type' => LoanOrder::class,
                        'taskable_id'   => $loan->id,
                        'manager_id'    => $def['manager']->id,
                        'description'   => 'Devolução dos equipamentos e verificação de estado após período de empréstimo',
                        'status'        => 'completed',
                        'priority'      => 'normal',
                        'start_date'    => $returnedAt->toDateString(),
                        'end_date'      => $returnedAt->toDateString(),
                        'created_at'    => $returnedAt,
                        'updated_at'    => $returnedAt,
                    ]);
                }
            }
        }

        $this->command->info('✅ Empréstimos semeados: ' . count($orders) . ' pedidos de empréstimo');
    }
}
