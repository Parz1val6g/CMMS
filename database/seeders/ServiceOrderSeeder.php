<?php

namespace Database\Seeders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus as SOStatus;
use App\Features\Clients\Models\Client;
use App\Features\Equipments\Models\Equipment;
use App\Features\Locations\Models\Location;
use App\Features\Sectors\Models\Sector;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    /**
     * Exhaustive state coverage:
     * - Every ServiceOrderStatus (4)
     * - Every Priority (4)
     * - All priority/status combinations (10 total)
     * - Some SOs linked to a client location (client_location_id)
     */
    public function run(): void
    {
        $clients      = Client::with('clientLocations')->get();
        $admin        = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $manager      = User::whereHas('roles', fn($q) => $q->where('name', 'manager'))->first();
        $attendant    = User::whereHas('roles', fn($q) => $q->where('name', 'attendant'))->first();
        $locations    = Location::whereDoesntHave('clientLocations')->get(); // standalone SO locations
        $serviceTypes = ServiceType::all();
        $sectors      = Sector::all();
        $categories   = ServiceOrderCategory::pluck('id', 'name');
        $loanableEq   = Equipment::where('is_loanable', true)
                            ->where('status', EquipmentStatus::ACTIVE->value)
                            ->first();

        if (!$admin || !$manager || $clients->isEmpty() || $locations->isEmpty() || $serviceTypes->isEmpty()) {
            $this->command->warn('ServiceOrderSeeder: missing prerequisite data — skipping.');
            return;
        }

        $now          = now();
        $twoMonthsAgo = (clone $now)->modify('-60 days');

        // Pick a client that has locations for the location-aware SOs
        $clientWithLocations = $clients->first(fn($c) => $c->clientLocations->isNotEmpty());
        $primaryLocation     = $clientWithLocations?->clientLocations->firstWhere('is_primary', true);
        $secondaryLocation   = $clientWithLocations?->clientLocations->firstWhere('is_primary', false);

        /**
         * Colunas: status, priority, manager, service_type name, title, description, category, loan flag, client_location
         *
         * Os campos 'title' e 'category' foram adicionados pelas migrações
         * 2026_06_02_000001 e 2026_06_02_000003 respetivamente.
         * 'service_type_id' foi removido da tabela pela 2026_06_02_100005;
         * o tipo de serviço é agora associado via service_order_sector_service_type.
         */
        $orders = [
            // ── Workflow standard — todas as combinações de estado × prioridade ──
            [
                'status'   => SOStatus::PENDING,
                'priority' => Priority::LOW,
                'manager'  => $manager,
                'st'       => 'Aprovação de Projetos',
                'title'    => 'Análise de projeto habitacional — Rua da Igreja',
                'desc'     => 'Análise de projeto de construção civil para edifício habitacional de 4 pisos na Rua da Igreja',
                'category' => 'Inspeção',
                'cl'       => null,
            ],
            [
                'status'   => SOStatus::PENDING,
                'priority' => Priority::NORMAL,
                'manager'  => $manager,
                'st'       => 'Emissão de Licenças',
                'title'    => 'Licença comercial — Rua Direita, Gouveia',
                'desc'     => 'Processamento de licença comercial para novo estabelecimento na Rua Direita, Gouveia',
                'category' => 'Instalação',
                'cl'       => $primaryLocation,
            ],
            [
                'status'   => SOStatus::IN_PROGRESS,
                'priority' => Priority::HIGH,
                'manager'  => $admin,
                'st'       => 'Iluminação Pública',
                'title'    => 'Substituição de luminárias LED — Av. dos Combatentes',
                'desc'     => 'Substituição de luminárias fundidas na Avenida dos Combatentes por LED de baixo consumo',
                'category' => 'Manutenção',
                'cl'       => $secondaryLocation,
            ],
            [
                'status'   => SOStatus::IN_PROGRESS,
                'priority' => Priority::URGENT,
                'manager'  => $admin,
                'st'       => 'Abastecimento de Água',
                'title'    => 'Rotura urgente na conduta — Rua do Mercado',
                'desc'     => 'Reparação de rotura na conduta principal de água na Rua do Mercado — intervenção urgente',
                'category' => 'Emergência',
                'cl'       => $primaryLocation,
            ],
            [
                'status'   => SOStatus::AWAITING_APPROVAL,
                'priority' => Priority::NORMAL,
                'manager'  => $manager,
                'st'       => 'Pavimentação',
                'title'    => 'Reparação de pavimento — Rua da Paz',
                'desc'     => 'Reparação de pavimento danificado na Rua da Paz — trabalhos concluídos, aguarda revisão do gestor',
                'category' => 'Reparação',
                'cl'       => null,
            ],
            [
                'status'   => SOStatus::COMPLETED,
                'priority' => Priority::HIGH,
                'manager'  => $admin,
                'st'       => 'Sinalização de Trânsito',
                'title'    => 'Pintura de passadeiras termoplásticas — Av. dos Combatentes',
                'desc'     => 'Pintura de passadeiras na Avenida dos Combatentes com tinta termoplástica — concluída e aprovada',
                'category' => 'Obra',
                'cl'       => null,
            ],
            [
                'status'   => SOStatus::CANCELLED,
                'priority' => Priority::LOW,
                'manager'  => $manager,
                'st'       => 'Vistoria de Imóveis',
                'title'    => 'Vistoria de habitabilidade cancelada — falta de documentação',
                'desc'     => 'Vistoria para licença de habitação cancelada por falta de documentação pelo requerente',
                'category' => 'Inspeção',
                'cl'       => null,
            ],
            [
                'status'   => SOStatus::CANCELLED,
                'priority' => Priority::NORMAL,
                'manager'  => $admin,
                'st'       => 'Limpeza Urbana',
                'title'    => 'Limpeza extraordinária cancelada — Praça do Município',
                'desc'     => 'Limpeza extraordinária na Praça do Município cancelada por condições meteorológicas adversas',
                'category' => 'Limpeza',
                'cl'       => null,
            ],
            // ── Cobertura adicional ──
            [
                'status'   => SOStatus::IN_PROGRESS,
                'priority' => Priority::LOW,
                'manager'  => $manager,
                'st'       => 'Manutenção de Jardins',
                'title'    => 'Manutenção de canteiros — Jardim Público',
                'desc'     => 'Manutenção de canteiros no Jardim Público com corte de relva e fertilização dos arbustos',
                'category' => 'Manutenção',
                'cl'       => $secondaryLocation,
            ],
            [
                'status'   => SOStatus::PENDING,
                'priority' => Priority::URGENT,
                'manager'  => $admin,
                'st'       => 'Saneamento Básico',
                'title'    => 'Desobstrução urgente de coletor — Rua do Mercado',
                'desc'     => 'Desobstrução urgente de coletor entupido na Rua do Mercado após colapso da tampa',
                'category' => 'Emergência',
                'cl'       => null,
            ],
            // ── Empréstimos (loan workflow) ──
            [
                'status'   => SOStatus::PENDING,
                'priority' => Priority::NORMAL,
                'manager'  => $manager,
                'st'       => null,
                'title'    => 'Empréstimo de compressor — Zona Industrial de Gouveia',
                'desc'     => 'Pedido de empréstimo de compressor Atlas Copco para obra na Zona Industrial de Gouveia',
                'category' => null,
                'cl'       => $primaryLocation,
                'loan'     => true,
            ],
            [
                'status'   => SOStatus::IN_PROGRESS,
                'priority' => Priority::HIGH,
                'manager'  => $admin,
                'st'       => null,
                'title'    => 'Empréstimo de martelo pneumático — Rua Direita',
                'desc'     => 'Empréstimo de martelo pneumático para demolição em obra municipal na Rua Direita',
                'category' => null,
                'cl'       => $secondaryLocation,
                'loan'     => true,
            ],
            [
                'status'   => SOStatus::COMPLETED,
                'priority' => Priority::NORMAL,
                'manager'  => $manager,
                'st'       => null,
                'title'    => 'Devolução de bomba de água Diesel concluída',
                'desc'     => 'Devolução de bomba de água Diesel após período de empréstimo de 15 dias — concluído',
                'category' => null,
                'cl'       => null,
                'loan'     => true,
            ],
            [
                'status'   => SOStatus::CANCELLED,
                'priority' => Priority::LOW,
                'manager'  => $admin,
                'st'       => null,
                'title'    => 'Empréstimo de vibrador de placas cancelado',
                'desc'     => 'Pedido de empréstimo de vibrador de placas cancelado por desistência do requerente',
                'category' => null,
                'cl'       => null,
                'loan'     => true,
            ],
        ];

        $counter = 0;

        foreach ($orders as $def) {
            $counter++;
            $year    = $now->format('Y');
            $process = 'OS/' . $year . '/GALLERY-' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);

            $createdAt = (clone $twoMonthsAgo)->modify('+' . (($counter - 1) * 4) . ' days');
            $endDate = match ($def['status']) {
                SOStatus::COMPLETED          => (clone $createdAt)->modify('+5 days'),
                SOStatus::AWAITING_APPROVAL  => (clone $createdAt)->modify('+4 days'),
                SOStatus::IN_PROGRESS        => (clone $createdAt)->modify('+2 days'),
                SOStatus::PENDING            => (clone $createdAt)->modify('+30 days'),
                SOStatus::CANCELLED          => (clone $createdAt)->modify('+10 days'),
            };

            // Resolve client: prefer the one whose client location is being used
            $client = $def['cl']?->client ?? $clients->first();

            // Resolve location: snapshot from client location if present, else use standalone
            $locationId = null;
            if ($def['cl'] !== null) {
                // Snapshot: copy the client location's address into a new Location row
                $source     = $def['cl']->location;
                $snapshot   = Location::create([
                    'parish_id'      => $source->parish_id,
                    'postal_code'    => $source->postal_code,
                    'street_address' => $source->street_address,
                    'landmark'       => $source->landmark,
                    'latitude'       => $source->latitude,
                    'longitude'      => $source->longitude,
                ]);
                $locationId = $snapshot->id;
            } else {
                $locationId = $locations->random()->id;
            }

            $serviceType = $def['st'] ? $serviceTypes->firstWhere('name', $def['st']) : null;
            $isLoan      = $def['loan'] ?? false;

            // Resolver category_id; empréstimos usam primeira categoria disponível como fallback
            $categoryId = isset($def['category']) && $def['category']
                ? ($categories[$def['category']] ?? $categories->first())
                : $categories->first();

            $so = ServiceOrder::create([
                'process'            => $process,
                'title'              => $def['title'] ?? null,
                'category_id'        => $categoryId,
                'client_id'          => $client->id,
                'client_location_id' => $def['cl']?->id,
                'manager_id'         => $def['manager']->id,
                'created_by'         => $attendant?->id,
                'location_id'        => $locationId,
                'priority'           => $def['priority']->value,
                'start_date'         => $createdAt,
                'end_date'           => $endDate,
                'status'             => $def['status']->value,
                'description'        => $def['desc'],
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ]);

            // Link a random sector with service types for non-loan orders
            if ($sectors->isNotEmpty() && !$isLoan) {
                $sector = $sectors->random();
                $so->sectors()->sync([$sector->id]);

                $typesForSector = $serviceType
                    ? $serviceTypes->where('sector_id', $sector->id)->first() ?? $serviceType
                    : $serviceTypes->where('sector_id', $sector->id)->first();

                if ($typesForSector) {
                    \Illuminate\Support\Facades\DB::table('service_order_sector_service_type')->insert([
                        'service_order_id' => $so->id,
                        'sector_id'        => $sector->id,
                        'service_type_id'  => $typesForSector->id,
                        'priority'         => $def['priority']->value,
                    ]);
                }
            } elseif ($sectors->isNotEmpty() && $isLoan) {
                $so->sectors()->sync([$sectors->random()->id]);
            }
        }
    }
}
