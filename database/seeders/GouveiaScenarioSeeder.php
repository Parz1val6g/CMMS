<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GouveiaScenarioSeeder extends Seeder
{
    private const GOUVEIA_CENTER_LAT = 40.4923;
    private const GOUVEIA_CENTER_LNG = -7.5936;
    private const OFFSET_RANGE = 0.005;

    public function run(): void
    {
        $parish  = DB::table('parishes')->inRandomOrder()->first();
        $clients = DB::table('clients')->get();
        $managers = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager']);
                    });
            })->get();
        $serviceTypes = DB::table('service_types')->get();

        if (!$parish || $clients->isEmpty() || $managers->isEmpty() || $serviceTypes->isEmpty()) {
            return;
        }

        $now = now();
        $processYear = $now->format('Y');

        // ── 20 Gouveia addresses with base coordinates ──
        $addresses = [
            'Praça do Município, 1',
            'Jardim Público',
            'Rua Direita, 25',
            'Bairro do Castelo, 10',
            'Avenida 25 de Abril, 42',
            'Rua Dr. Afonso Costa, 7',
            'Largo de São Pedro, 3',
            'Rua da Misericórdia, 15',
            'Avenida da Liberdade, 30',
            'Rua do Mercado, 8',
            'Rua de Santa Maria, 12',
            'Rua Nova, 20',
            'Bairro da Ponte, 5',
            'Rua da Fonte, 18',
            'Travessa do Rossio, 4',
            'Rua do Comércio, 22',
            'Largo do Município, 2',
            'Rua da Igreja, 9',
            'Bairro do Sobrado, 14',
            'Estrada Nacional 17, km 184',
        ];

        $landmarks = [
            'Edifício da Câmara Municipal',
            'Zona verde central',
            'Arteria principal do centro histórico',
            'Zona histórica junto ao castelo',
            'Rotunda da entrada da cidade',
            'Junto aos Paços do Concelho',
            'Igreja de São Pedro',
            'Antigo hospital',
            'Saída para Mangualde',
            'Praça do Mercado Municipal',
            'Zona residencial histórica',
            'Bairro novo',
            'Junto à ponte sobre o Rio Mondego',
            'Largo da Fonte Velha',
            'Cruzamento central',
            'Zona comercial tradicional',
            'Frente aos Paços do Município',
            'Igreja Matriz',
            'Urbanização recente',
            'Via principal de acesso à cidade',
        ];

        // Priority distribution: 5 urgent, 10 normal, 5 low
        $priorities = array_merge(
            array_fill(0, 5, 'urgent'),
            array_fill(0, 10, 'normal'),
            array_fill(0, 5, 'low')
        );

        // Descriptions per priority
        $urgentDescriptions = [
            'Rotura de conduta principal na zona central',
            'Falha elétrica nos semáforos da rotunda',
            'Queda de árvore de grande porte na via pública',
            'Derrocada de talude junto ao Bairro do Castelo',
            'Inundação na Praça do Município por rotura de conduta',
        ];

        $normalDescriptions = [
            'Manutenção de pavimentos na Avenida 25 de Abril',
            'Poda de árvores no Jardim Público',
            'Reparação de calçada na Rua Direita',
            'Substituição de luminárias na Avenida da Liberdade',
            'Limpeza de coletores no Bairro da Ponte',
            'Reparação de passeios na Rua do Mercado',
            'Manutenção do sistema de rega do Jardim Público',
            'Pintura de passadeiras na Rua Nova',
            'Substituição de lancis na Travessa do Rossio',
            'Reparação de gradeamentos no Largo de São Pedro',
        ];

        $lowDescriptions = [
            'Pintura de sinalética vertical na zona histórica',
            'Limpeza de sarjetas na Rua da Fonte',
            'Substituição de tampas de saneamento na Rua do Comércio',
            'Plantação de arbustos no Bairro do Sobrado',
            'Remoção de monos e resíduos volumosos na Estrada Nacional 17',
        ];

        $allDescriptions = array_merge($urgentDescriptions, $normalDescriptions, $lowDescriptions);

        $locationIds = [];
        $counter = 1000;

        // ── Step 1: Create 20 Locations ──
        foreach ($addresses as $i => $address) {
            $latOffset = (mt_rand(-1000, 1000) / 100000) * self::OFFSET_RANGE * 200;
            $lngOffset = (mt_rand(-1000, 1000) / 100000) * self::OFFSET_RANGE * 200;

            $id = Str::uuid();
            $locationIds[] = $id;

            DB::table('locations')->insert([
                'id'             => $id,
                'parish_id'      => $parish->id,
                'postal_code'    => '6290-' . str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT),
                'street_address' => $address,
                'landmark'       => $landmarks[$i],
                'latitude'       => self::GOUVEIA_CENTER_LAT + $latOffset,
                'longitude'      => self::GOUVEIA_CENTER_LNG + $lngOffset,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // ── Step 2: Create 20 Service Orders ──
        for ($i = 0; $i < 20; $i++) {
            $client     = $clients->random();
            $manager    = $managers->random();
            $serviceType = $serviceTypes->random();
            $locationId = $locationIds[$i];
            $priority   = $priorities[$i];
            $description = $allDescriptions[$i];

            $counter++;
            $process = sprintf('OS/%s/%04d', $processYear, $counter);

            $status = $priority === 'urgent'
                ? 'in_progress'
                : 'pending';

            DB::table('service_orders')->insert([
                'id'             => Str::uuid(),
                'process'        => $process,
                'client_id'      => $client->id,
                'manager_id'     => $manager->id,
                'location_id'    => $locationId,
                'service_type_id'=> $serviceType->id,
                'priority'       => $priority,
                'start_date'     => $now->toDateString(),
                'end_date'       => $now->toDateString(),
                'status'         => $status,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
