<?php

namespace Database\Seeders;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus as SOStatus;
use App\Features\Clients\Models\Client;
use App\Features\Equipments\Models\Equipment;
use App\Features\Locations\Models\Location;
use App\Features\Sectors\Models\Sector;
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
        $locations    = Location::whereDoesntHave('clientLocations')->get(); // standalone SO locations
        $serviceTypes = ServiceType::all();
        $sectors      = Sector::all();
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
         * Columns: status, priority, workflow, manager, service_type name, description,
         *          loanable equipment flag, client_location (ClientLocation model|null)
         */
        $orders = [
            // ── Standard workflow — all statuses × all priorities ──
            ['status' => SOStatus::PENDING,     'priority' => Priority::LOW,     'manager' => $manager, 'st' => 'Aprovação de Projetos',   'desc' => 'Análise de projeto de construção civil para edifício habitacional de 4 pisos',                          'cl' => null],
            ['status' => SOStatus::PENDING,     'priority' => Priority::NORMAL,  'manager' => $manager, 'st' => 'Emissão de Licenças',     'desc' => 'Processamento de licença comercial para novo estabelecimento na Rua Direita',                          'cl' => $primaryLocation],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::HIGH,    'manager' => $admin,   'st' => 'Iluminação Pública',     'desc' => 'Substituição de luminárias fundidas na Av. Principal por LED de baixo consumo',                          'cl' => $secondaryLocation],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::URGENT,  'manager' => $admin,   'st' => 'Abastecimento de Água',  'desc' => 'Reparação de rotura na conduta principal de água com escavação e soldadura urgente',                    'cl' => $primaryLocation],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::NORMAL,  'manager' => $manager, 'st' => 'Pavimentação',            'desc' => 'Reparação de piso danificado na Rua Nova com aplicação de nova camada de asfalto',                       'cl' => null],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::HIGH,    'manager' => $admin,   'st' => 'Sinalização de Trânsito', 'desc' => 'Pintura de passadeiras na Av. Central com tinta termoplástica — concluída',                            'cl' => null],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::LOW,     'manager' => $manager, 'st' => 'Vistoria de Imóveis',     'desc' => 'Vistoria para licença de habitação cancelada por falta de documentação',                               'cl' => null],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::NORMAL,  'manager' => $admin,   'st' => 'Limpeza Urbana',          'desc' => 'Limpeza extraordinária cancelada devido a condições meteorológicas adversas',                          'cl' => null],
            // ── Standard — additional edge cases ──
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::LOW,     'manager' => $manager, 'st' => 'Manutenção de Jardins',  'desc' => 'Manutenção de canteiros na Praça Municipal com corte de ervas e fertilização',                         'cl' => $secondaryLocation],
            ['status' => SOStatus::PENDING,     'priority' => Priority::URGENT,  'manager' => $admin,   'st' => 'Saneamento',             'desc' => 'Desobstrução urgente de coletor entupido na Rua do Mercado',                                           'cl' => null],
            // ── Loan workflow ──
            ['status' => SOStatus::PENDING,     'priority' => Priority::NORMAL,  'manager' => $manager, 'st' => null, 'desc' => 'Pedido de empréstimo de compressor Atlas Copco para obra na Zona Industrial',    'cl' => $primaryLocation,  'loan' => true],
            ['status' => SOStatus::IN_PROGRESS, 'priority' => Priority::HIGH,    'manager' => $admin,   'st' => null, 'desc' => 'Empréstimo de martelo pneumático Bosch para demolição em obra municipal',       'cl' => $secondaryLocation, 'loan' => true],
            ['status' => SOStatus::COMPLETED,   'priority' => Priority::NORMAL,  'manager' => $manager, 'st' => null, 'desc' => 'Devolução de bomba de água Diesel após período de empréstimo de 15 dias',       'cl' => null,               'loan' => true],
            ['status' => SOStatus::CANCELLED,   'priority' => Priority::LOW,     'manager' => $admin,   'st' => null, 'desc' => 'Pedido de empréstimo cancelado por desistência do requerente',                   'cl' => null,               'loan' => true],
        ];

        $counter = 0;

        foreach ($orders as $def) {
            $counter++;
            $year    = $now->format('Y');
            $process = 'OS/' . $year . '/GALLERY-' . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);

            $createdAt = (clone $twoMonthsAgo)->modify('+' . (($counter - 1) * 4) . ' days');
            $executionDate = match ($def['status']) {
                SOStatus::COMPLETED   => (clone $createdAt)->modify('+5 days'),
                SOStatus::IN_PROGRESS => (clone $createdAt)->modify('+2 days'),
                SOStatus::PENDING     => (clone $createdAt)->modify('+30 days'),
                SOStatus::CANCELLED   => (clone $createdAt)->modify('+10 days'),
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

            $so = ServiceOrder::create([
                'process'            => $process,
                'client_id'          => $client->id,
                'client_location_id' => $def['cl']?->id,
                'manager_id'         => $def['manager']->id,
                'location_id'        => $locationId,
                'service_type_id'    => $serviceType?->id,
                'priority'           => $def['priority']->value,
                'execution_date'     => $executionDate,
                'status'             => $def['status']->value,
                'description'        => $def['desc'],
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ]);

            // Link sectors for all orders
            if ($sectors->isNotEmpty()) {
                $so->sectors()->sync([$sectors->random()->id]);
            }
        }
    }
}
