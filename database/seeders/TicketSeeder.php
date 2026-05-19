<?php

namespace Database\Seeders;

use App\Core\Enums\TicketPriority;
use App\Core\Enums\TicketStatus;
use App\Features\Clients\Models\Client;
use App\Features\Locations\Models\Location;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Exhaustive Ticket coverage: all status×priority combinations
     * with realistic Portuguese citizen complaint scenarios.
     *
     * Converted tickets link to an existing ServiceOrder (ServiceOrderSeeder
     * must run first). Cancelled tickets include a reason in the description.
     */
    public function run(): void
    {
        $clients       = Client::all();
        $serviceTypes  = ServiceType::all();
        $locations     = Location::all();
        $serviceOrders = ServiceOrder::where('status', '!=', 'cancelled')->get();
        $ticketManager = User::whereHas('roles', fn($q) => $q->where('name', 'ticket_manager'))->first();

        if ($clients->isEmpty() || !$ticketManager) {
            $this->command->warn('TicketSeeder: missing clients or ticket_manager — skipping.');
            return;
        }

        $now         = now();
        $oneMonthAgo = (clone $now)->modify('-30 days');

        /**
         * Columns: status, priority, description, client_offset, service_type name, location_offset, converted
         *
         * Tickets are spread across clients deterministically (client_offset).
         * Location and service_type use meaningful pairings for realism.
         */
        $tickets = [
            // ── OPEN tickets — all priorities ──
            [
                'status'      => TicketStatus::OPEN,
                'priority'    => TicketPriority::LOW,
                'description' => 'Solicito substituição de placa de sinalética vertical na Rua do Comércio. A placa está ilegível devido a graffiti.',
                'st_name'     => 'Sinalização de Trânsito',
            ],
            [
                'status'      => TicketStatus::OPEN,
                'priority'    => TicketPriority::NORMAL,
                'description' => 'Contentor de resíduos a transbordar há 4 dias na Praça do Município. Solicitamos reforço urgente da frequência de recolha.',
                'st_name'     => 'Recolha de Resíduos',
            ],
            [
                'status'      => TicketStatus::OPEN,
                'priority'    => TicketPriority::HIGH,
                'description' => 'Luminária pública apagada há mais de uma semana na Avenida dos Combatentes, em frente ao nº 45. A rua fica completamente às escuras.',
                'st_name'     => 'Iluminação Pública',
            ],
            [
                'status'      => TicketStatus::OPEN,
                'priority'    => TicketPriority::URGENT,
                'description' => 'Árvore de grande porte com ramos partidos sobre a via pública após o temporal de ontem. Risco de queda iminente sobre viaturas e transeuntes.',
                'st_name'     => 'Manutenção de Jardins',
            ],
            // ── IN_PROGRESS tickets ──
            [
                'status'      => TicketStatus::IN_PROGRESS,
                'priority'    => TicketPriority::LOW,
                'description' => 'Solicito pintura de banco de jardim danificado no Jardim Público. Já foi feita vistoria e aguarda alocação de equipa de manutenção.',
                'st_name'     => 'Mobiliário Urbano',
            ],
            [
                'status'      => TicketStatus::IN_PROGRESS,
                'priority'    => TicketPriority::NORMAL,
                'description' => 'Tampa de saneamento partida na Rua da Fonte, junto ao Largo. Equipa técnica já foi notificada e aguarda-se intervenção.',
                'st_name'     => 'Saneamento Básico',
            ],
            [
                'status'      => TicketStatus::IN_PROGRESS,
                'priority'    => TicketPriority::HIGH,
                'description' => 'Fuga de água visível na conduta da Rua Direita há 3 dias. Caudal significativo a correr pela via. Equipa já está no local a avaliar.',
                'st_name'     => 'Abastecimento de Água',
            ],
            [
                'status'      => TicketStatus::IN_PROGRESS,
                'priority'    => TicketPriority::URGENT,
                'description' => 'Derrocada de talude junto ao Bairro do Castelo após chuvas intensas. Via parcialmente obstruída. Proteção Civil e máquinas já mobilizadas.',
                'st_name'     => 'Drenagem Pluvial',
            ],
            // ── CONVERTED tickets — link to real ServiceOrders ──
            [
                'status'      => TicketStatus::CONVERTED,
                'priority'    => TicketPriority::LOW,
                'description' => 'Solicito limpeza de sarjetas entupidas na Rua do Parque. Convertido para OS de limpeza preventiva.',
                'st_name'     => 'Drenagem Pluvial',
                'converted'   => true,
            ],
            [
                'status'      => TicketStatus::CONVERTED,
                'priority'    => TicketPriority::NORMAL,
                'description' => 'Buraco na calçada portuguesa no Largo de São Pedro. Vários peões já tropeçaram. Convertido para OS de calcetamento.',
                'st_name'     => 'Calcetamento',
                'converted'   => true,
            ],
            [
                'status'      => TicketStatus::CONVERTED,
                'priority'    => TicketPriority::HIGH,
                'description' => 'Passadeira completamente apagada em frente à Escola Primária. Crianças em perigo. Convertido para OS urgente de sinalização.',
                'st_name'     => 'Sinalização de Trânsito',
                'converted'   => true,
            ],
            [
                'status'      => TicketStatus::CONVERTED,
                'priority'    => TicketPriority::URGENT,
                'description' => 'Rotura de conduta principal de água na Praça do Município. Convertido para OS de reparação urgente de abastecimento.',
                'st_name'     => 'Abastecimento de Água',
                'converted'   => true,
            ],
            // ── CANCELLED tickets ──
            [
                'status'      => TicketStatus::CANCELLED,
                'priority'    => TicketPriority::LOW,
                'description' => 'Solicito plantação de arbustos no talude da Estrada Nacional. Cancelado — a área pertence à Estradas de Portugal e não à autarquia.',
                'st_name'     => 'Manutenção de Jardins',
            ],
            [
                'status'      => TicketStatus::CANCELLED,
                'priority'    => TicketPriority::NORMAL,
                'description' => 'Pedido de substituição de tampas de saneamento na zona industrial. Cancelado — o munícipe apresentou o mesmo pedido em duplicado.',
                'st_name'     => 'Saneamento Básico',
            ],
            [
                'status'      => TicketStatus::CANCELLED,
                'priority'    => TicketPriority::HIGH,
                'description' => 'Solicito remoção de monos volumosos abandonados no Bairro da Ponte. Cancelado — resíduos já foram recolhidos pela equipa de limpeza urbana.',
                'st_name'     => 'Limpeza Urbana',
            ],
            [
                'status'      => TicketStatus::CANCELLED,
                'priority'    => TicketPriority::URGENT,
                'description' => 'Pedido de intervenção urgente em edifício devoluto na Rua da Sé. Cancelado — a competência é da Autoridade de Saúde e Proteção Civil.',
                'st_name'     => 'Vistoria de Imóveis',
            ],
        ];

        $convertedCount = 0;

        foreach ($tickets as $i => $def) {
            $createdAt = (clone $oneMonthAgo)->modify('+' . $i . ' days');
            $client    = $clients->get($i % $clients->count());
            $serviceType = $serviceTypes->firstWhere('name', $def['st_name']);
            $location  = $locations->isNotEmpty() ? $locations->get($i % $locations->count()) : null;

            // For converted tickets, link to a ServiceOrder
            $serviceOrderId = null;
            if (($def['converted'] ?? false) && $serviceOrders->isNotEmpty()) {
                $so = $serviceOrders->get($convertedCount % $serviceOrders->count());
                $serviceOrderId = $so->id;
                $convertedCount++;
            }

            DB::table('tickets')->insert([
                'id'                => \Illuminate\Support\Str::uuid(),
                'description'       => $def['description'],
                'client_id'         => $client->id,
                'service_type_id'   => $serviceType?->id,
                'priority'          => $def['priority']->value,
                'status'            => $def['status']->value,
                'ticket_manager_id' => $ticketManager->id,
                'service_order_id'  => $serviceOrderId,
                'location_id'       => $location?->id,
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ]);
        }

        $this->command->info('✅ Tickets semeados: ' . count($tickets) . ' tickets (' . $convertedCount . ' convertidos)');
    }
}
